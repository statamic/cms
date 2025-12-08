<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;

use function Laravel\Prompts\spin;

class SetupCpVite extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:setup-cp-vite {--only-necessary : Only configure the necessary parts for Vite to work with the Control Panel}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configures Vite for the Control Panel';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $this
            ->installDependencies()
            ->addScriptsToPackageJson()
            ->publishViteConfig()
            ->publishStubs()
            ->publishDevBuild()
            ->appendViteSnippetToAppServiceProvider();
    }

    private function installDependencies(): self
    {
        spin(
            callback: function () {
                $packageJsonPath = base_path('package.json');
                $contents = File::json($packageJsonPath);

                $installedDependencies = collect($contents['dependencies'] ?? [])->merge($contents['devDependencies'] ?? []);

                if (! $installedDependencies->contains('vite')) {
                    $contents['devDependencies']['vite'] = '^7.0.4';
                }

                if (! $installedDependencies->contains('@statamic/cms')) {
                    $contents['dependencies']['@statamic/cms'] = 'file:./vendor/statamic/cms/resources/dist-package';
                }

                File::put($packageJsonPath, json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                Process::path(base_path())->run('npm install', function (string $type, string $buffer) {
                    echo $buffer;
                });
            },
            message: 'Installing dependencies...'
        );

        $this->components->info('Installed dependencies');

        return $this;
    }

    private function addScriptsToPackageJson(): self
    {
        $packageJsonPath = base_path('package.json');
        $contents = File::json($packageJsonPath);

        $contents['scripts'] = [
            ...$contents['scripts'] ?? [],
            'cp:dev' => 'vite build --config vite-cp.config.js --watch',
            'cp:build' => 'vite build --config vite-cp.config.js',
        ];

        File::put($packageJsonPath, json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->components->info('Added cp:dev and cp:build scripts to package.json');

        return $this;
    }

    private function publishViteConfig(): self
    {
        if (File::exists(base_path('vite-cp.config.js'))) {
            $this->components->warn('vite-cp.config.js already exists. Skipping publishing.');

            return $this;
        }

        File::put(
            base_path('vite-cp.config.js'),
            File::get(__DIR__.'/stubs/app/vite-cp.config.js.stub')
        );

        $this->components->info('Published vite-cp.config.js');

        return $this;
    }

    private function publishStubs(): self
    {
        if (! File::exists(resource_path('css/cp.css'))) {
            File::ensureDirectoryExists(resource_path('css'));

            File::put(resource_path('css/cp.css'), File::get(__DIR__.'/stubs/app/cp.css.stub'));
        }

        if (! File::exists(resource_path('js/cp.js'))) {
            File::ensureDirectoryExists(resource_path('js'));

            File::put(resource_path('js/cp.js'), File::get(__DIR__.'/stubs/app/cp.js.stub'));
        }

        if (
            ! File::exists(resource_path('js/components/fieldtypes/ExampleFieldtype.vue'))
            && ! $this->option('only-necessary')
        ) {
            File::ensureDirectoryExists(resource_path('js/components/fieldtypes'));

            File::put(resource_path('js/components/fieldtypes/ExampleFieldtype.vue'), File::get(__DIR__.'/stubs/fieldtype.vue.stub'));
        }

        $this->components->info('Published stubs for Control Panel CSS & JavaScript.');

        return $this;
    }

    private function publishDevBuild(): self
    {
        $this->call('vendor:publish', ['--tag' => 'statamic-cp-dev']);

        return $this;
    }

    private function appendViteSnippetToAppServiceProvider(): self
    {
        spin(
            callback: function () {
                $this->addImportToAppServiceProvider('Statamic\\Statamic');

                $this->addCodeToAppServiceProvidersBootMethod(<<<'PHP'
    Statamic::vite('app', [
            'input' => [
                'resources/js/cp.js',
                'resources/css/cp.css',
            ],
            'buildDirectory' => 'vendor/app',
        ]);
PHP);
            },
            message: 'Adding Statamic::vite() snippet to AppServiceProvider...'
        );

        $this->components->info('Added Statamic::vite() snippet to AppServiceProvider.');

        return $this;
    }

    private function addImportToAppServiceProvider(string $class): void
    {
        $contents = File::get(app_path('Providers/AppServiceProvider.php'));

        $lines = explode("\n", $contents);

        $useLines = $originalUseLines = array_filter($lines, fn ($line) => Str::startsWith($line, 'use '));
        $useLines[] = "use $class;";

        // Filter out duplicate imports.
        $useLines = array_unique($useLines);

        // Sort the imports alphabetically.
        usort($useLines, fn ($a, $b) => strcasecmp(substr($a, 4), substr($b, 4)));

        // Get the position of the first and last "use " lines.
        $firstUseLine = array_key_first($originalUseLines);
        $lastUseLine = array_key_last($originalUseLines);

        // Replace everything in between the first and last "use " lines with the new imports.
        $contents = implode("\n", array_merge(
            array_slice($lines, 0, $firstUseLine),
            $useLines,
            array_slice($lines, $lastUseLine + 1)
        ));

        File::put(app_path('Providers/AppServiceProvider.php'), $contents);
    }

    private function addCodeToAppServiceProvidersBootMethod(string $code): void
    {
        $contents = File::get(app_path('Providers/AppServiceProvider.php'));

        $starters = [
            <<<'PHP'
public function boot()
    {
PHP,
            <<<'PHP'
public function boot(): void
    {
PHP,
            <<<'PHP'
public function boot() {
PHP,
            <<<'PHP'
public function boot(): void {
PHP,
        ];

        // Ensure the boot() method exists.
        if (! Str::contains($contents, $starters)) {
            throw new \Exception('Code could not be injected. No boot method found in AppServiceProvider.');
        }

        // Ensure this code snippet hasn't already been injected.
        if (Str::contains(str_replace([' ', "\n"], '', $contents), str_replace([' ', "\n"], '', $code))) {
            throw new \Exception('Code has already been injected.');
        }

        foreach ($starters as $starter) {
            if (Str::contains($contents, $starter)) {
                $contents = Str::replaceFirst($starter, $starter."\n    $code\n", $contents);
                break;
            }
        }

        File::put(app_path('Providers/AppServiceProvider.php'), $contents);
    }
}
