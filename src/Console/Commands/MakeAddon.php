<?php

namespace Statamic\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Statamic\Console\RunsInPlease;
use Statamic\Console\ValidatesInput;
use Statamic\Rules\ComposerPackage;
use Statamic\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeAddon extends GeneratorCommand
{
    use RunsInPlease, ValidatesInput;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $name = 'statamic:make:addon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new addon';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Addon';

    /**
     * The composer package.
     *
     * @var string
     */
    protected $package;

    /**
     * The vendor slug.
     *
     * @var string
     */
    protected $vendorSlug;

    /**
     * The name slug.
     *
     * @var string
     */
    protected $nameSlug;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->validationFails($this->package = $this->argument('addon'), new ComposerPackage)) {
            return;
        }

        $this->normalizePackage();

        if (! $this->option('force') && $this->addonAlreadyExists()) {
            $this->error('Addon already exists!');

            return;
        }

        $this
            ->generateComposerJson()
            ->generateAddonFiles()
            ->addRepositoryPath()
            ->installAddon()
            ->generateOptional();

        $relativePath = $this->getRelativePath($this->addonPath());

        $this->info("Your addon package is ready: <comment>{$relativePath}</comment>");
        $this->line('Learn how to build addons in our docs: <comment>https://statamic.dev/extending/addons</comment>');
    }

    /**
     * Normalize package, and set reusable slugs.
     */
    protected function normalizePackage()
    {
        $parts = explode('/', $this->package);

        $this->vendorSlug = str_slug(snake_case($parts[0]));
        $this->nameSlug = str_slug(snake_case($parts[1]));
        $this->package = "{$this->vendorSlug}/{$this->nameSlug}";
    }

    /**
     * Generate composer.json.
     *
     * @return $this
     */
    protected function generateComposerJson()
    {
        $json = $this->files->get($this->getStub('addon/composer.json.stub'));

        $json = str_replace('DummyNamespace', str_replace('\\', '\\\\', $this->addonNamespace()), $json);
        $json = str_replace('dummy/package', $this->package, $json);
        $json = str_replace('DummyTitle', $this->addonTitle(), $json);

        $this->files->put($this->addonPath('composer.json'), $json);

        $this->line('Composer config created successfully.');

        return $this;
    }

    /**
     * Generate addon package files.
     *
     * @return $this
     */
    protected function generateAddonFiles()
    {
        $files = [
            'addon/provider.php.stub' => 'src/ServiceProvider.php',
            'addon/.gitignore.stub' => '.gitignore',
            'addon/README.md.stub' => 'README.md',
        ];

        $data = [
            'name' => $this->addonTitle(),
            'package' => $this->package,
            'namespace' => $this->addonNamespace(),
        ];

        $this->info('Scaffolding the addon package boilerplate...');
        $this->info('-------------------------------------------------------');
        foreach ($files as $stub => $file) {
            $this->createFromStub($stub, $this->addonPath($file), $data);
            $this->line($this->addonPath($file));
        }

        return $this;
    }

    /**
     * Run optional generators.
     *
     * @return $this
     */
    protected function generateOptional()
    {
        collect(['fieldtype', 'scope', 'modifier', 'tag', 'widget', 'action', 'filter'])
            ->filter(function ($type) {
                return $this->option($type) || $this->option('all');
            })
            ->each(function ($type) {
                $this->runOptionalAddonGenerator($type);
            });

        return $this;
    }

    /**
     * Add repository path to app's composer.json file.
     *
     * @return $this
     */
    protected function addRepositoryPath()
    {
        $decoded = json_decode($this->files->get(base_path('composer.json')), true);

        $decoded['repositories'][] = [
            'type' => 'path',
            'url' => "addons/{$this->vendorSlug}/{$this->nameSlug}",
        ];

        $json = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        $this->files->put(base_path('composer.json'), $json);

        $this->info('Repository added to your application composer configuration successfully.');

        return $this;
    }

    /**
     * Install addon.
     *
     * @return $this
     */
    protected function installAddon()
    {
        $this->info('Installing your addon...');

        $output = Composer::runAndOperateOnOutput(['require', $this->package], function ($output) {
            return $this->outputFromSymfonyProcess($output);
        });

        if (! Str::contains($output, "Discovered Addon: {$this->package}")) {
            $this->error('An error was encountered while installing your addon!');
        } else {
            $this->info('Addon installed successfully.');
        }

        return $this;
    }

    /**
     * Run optional addon generator command.
     *
     * @param string $type
     */
    protected function runOptionalAddonGenerator($type)
    {
        $prefix = $this->runningInPlease ? '' : 'statamic:';
        $arguments = ['name' => studly_case($this->nameSlug), 'addon' => $this->addonPath()];

        if ($this->option('force')) {
            $arguments['--force'] = true;
        }

        $this->call("{$prefix}make:{$type}", $arguments);
    }

    /**
     * Determine if addon already exists.
     *
     * @return bool
     */
    protected function addonAlreadyExists()
    {
        return $this->files->exists($this->addonPath(null, false));
    }

    /**
     * Build absolute path for an addon or addon file.
     *
     * @param string|null $file
     * @return string
     */
    protected function addonPath($file = null, $makeDirectory = true)
    {
        $path = config('statamic.system.addons_path')."/{$this->vendorSlug}/{$this->nameSlug}";

        if ($file) {
            $path .= "/{$file}";
        }

        if ($makeDirectory) {
            $this->makeDirectory($path);
        }

        return $path;
    }

    /**
     * Build addon namespace.
     *
     * @return string
     */
    protected function addonNamespace()
    {
        return studly_case($this->vendorSlug).'\\'.studly_case($this->nameSlug);
    }

    /**
     * Get addon title.
     *
     * @return string
     */
    protected function addonTitle()
    {
        return str_replace('-', ' ', title_case($this->nameSlug));
    }

    /**
     * Clean up symfony process output and output to cli.
     *
     * @param string $output
     * @return string
     */
    private function outputFromSymfonyProcess(string $output)
    {
        // Remove terminal color codes.
        $output = preg_replace('/\\e\[[0-9]+m/', '', $output);

        // Remove new lines.
        $output = preg_replace('/[\r\n]+$/', '', $output);

        // If not a blank line, output to terminal.
        if (! empty(trim($output))) {
            $this->line($output);
        }

        return $output;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['addon', InputArgument::REQUIRED, 'The package name of the addon (ie. john/my-addon)'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['all',       'a', InputOption::VALUE_NONE, 'Generate everything and the kitchen sink with the addon'],
            ['action',     null, InputOption::VALUE_NONE, 'Create a new action with the addon'],
            ['fieldtype', 'f', InputOption::VALUE_NONE, 'Create a new fieldtype with the addon'],
            ['filter',     null, InputOption::VALUE_NONE, 'Create a new filter with the addon'],
            ['scope',     's', InputOption::VALUE_NONE, 'Create a new scope with the addon'],
            ['modifier',  'm', InputOption::VALUE_NONE, 'Create a new modifier with the addon'],
            ['tag',       't', InputOption::VALUE_NONE, 'Create a new tag with the addon'],
            ['widget',    'w', InputOption::VALUE_NONE, 'Create a new widget with the addon'],
        ]);
    }
}
