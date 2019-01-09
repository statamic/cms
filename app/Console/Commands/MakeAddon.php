<?php

namespace Statamic\Console\Commands;

use Statamic\Console\RunsInPlease;
use Facades\Statamic\Console\Processes\Composer;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MakeAddon extends GeneratorCommand
{
    use RunsInPlease;

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
     * The name of the addon.
     *
     * @var string
     */
    protected $addonName;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->addonName = $this->getNameInput();

        $this->generateComposerJson();
        $this->generateServiceProvider();
        $this->generateOptional();
        $this->addRepositoryPath();
        $this->composerRequireAddon();

        $relativePath = $this->getRelativePath($this->addonPath());

        $this->info('Addon created successfully.');
        $this->comment("Your addon files await at: {$relativePath}");
    }

    /**
     * Generate composer.json.
     */
    protected function generateComposerJson()
    {
        $json = $this->files->get($this->getStub('addon/composer.json.stub'));

        $json = str_replace('DummyNamespace', str_replace('\\', '\\\\', $this->addonNamespace()), $json);
        $json = str_replace('dummy-slug', $this->addonSlug(), $json);
        $json = str_replace('DummyTitle', $this->addonTitle(), $json);

        $this->files->put($this->addonPath('composer.json'), $json);

        $this->info('Composer configuration created successfully.');
    }

    /**
     * Generate service provider.
     */
    protected function generateServiceProvider()
    {
        $provider = $this->files->get($this->getStub('addon/provider.php.stub'));

        $provider = str_replace('DummyNamespace', $this->addonNamespace(), $provider);

        $this->files->put($this->addonPath('src/ServiceProvider.php'), $provider);

        $this->info('Service provider created successfully.');
    }

    /**
     * Add repository path to app's composer.json file.
     */
    protected function addRepositoryPath()
    {
        $decoded = json_decode($this->files->get(base_path('composer.json')), true);

        $decoded['repositories'][] = [
            'type' => 'path',
            'url' => 'addons/' . $this->addonSlug(),
        ];

        $json = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        $this->files->put(base_path('composer.json'), $json);

        $this->info('Repository added to your application composer configuration successfully.');
    }

    /**
     * Composer require addon.
     */
    protected function composerRequireAddon()
    {
        $package = 'local/' . $this->addonSlug();

        $this->info('Installing your addon...');

        $output = Composer::runAndOperateOnOutput(['require', $package], function ($output) {
            return $this->outputFromSymfonyProcess($output);
        });

        if (! str_contains($output, "Discovered Addon: {$package}")) {
            $this->error('An error was encountered while installing your addon!');
        }
    }

    /**
     * Run optional generators.
     */
    protected function generateOptional()
    {
        collect(['fieldtype', 'filter', 'modifier', 'tag', 'widget'])
            ->filter(function ($type) {
                return $this->option($type) || $this->option('all');
            })
            ->each(function ($type) {
                $this->runExternalGenerator($type);
            });
    }

    /**
     * Run external generator command.
     *
     * @param string $type
     */
    protected function runExternalGenerator($type)
    {
        $prefix = $this->runningInPlease ? '' : 'statamic:';
        $arguments = ['name' => $this->addonName, 'addon' => $this->addonPath('src')];

        $this->call("{$prefix}make:{$type}", $arguments);
    }

    /**
     * Build absolute path for an addon or addon file, and ensure folder structure exists.
     *
     * @param string|null $file
     * @return string
     */
    protected function addonPath($file = null)
    {
        $path = config('statamic.system.addons_path') . '/' . $this->addonSlug();

        if ($file) {
            $path .= "/{$file}";
        }

        $this->makeDirectory($path);

        return $path;
    }

    /**
     * Build addon namespace.
     *
     * @return string
     */
    protected function addonNamespace()
    {
        return "Local\\{$this->addonName}";
    }

    /**
     * Get addon slug.
     *
     * @return string
     */
    protected function addonSlug()
    {
        return str_slug(snake_case($this->addonName));
    }

    /**
     * Get addon title.
     *
     * @return string
     */
    protected function addonTitle()
    {
        return str_replace('-', ' ', title_case($this->addonSlug()));
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
            ['name', InputArgument::REQUIRED, 'The name of the addon'],
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
            ['fieldtype', 'f', InputOption::VALUE_NONE, 'Create a new fieldtype with the addon'],
            ['filter',    'r', InputOption::VALUE_NONE, 'Create a new filter with the addon'],
            ['modifier',  'm', InputOption::VALUE_NONE, 'Create a new modifier with the addon'],
            ['tag',       't', InputOption::VALUE_NONE, 'Create a new tag with the addon'],
            ['widget',    'w', InputOption::VALUE_NONE, 'Create a new widget with the addon'],
        ]);
    }
}
