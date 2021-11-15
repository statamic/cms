<?php

namespace Statamic\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\Processes\Exceptions\ProcessException;
use Statamic\Console\RunsInPlease;
use Statamic\Console\ValidatesInput;
use Statamic\Rules\ComposerPackage;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeAddon extends GeneratorCommand
{
    use RunsInPlease, ValidatesInput, EnhancesCommands;

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

        try {
            $this
                ->generateAddonFiles()
                ->installAddon()
                ->generateOptional();
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $relativePath = $this->getRelativePath($this->addonPath());

        $this->output->newLine();
        $this->info("🎉 Your addon package is ready: <comment>{$relativePath}</comment>");
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

        return $this;
    }

    /**
     * Generate addon package files.
     *
     * @return $this
     */
    protected function generateAddonFiles()
    {
        $this->line('Creating addon...');

        $this->generateComposerJson();

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

        foreach ($files as $stub => $file) {
            $this->createFromStub($stub, $this->addonPath($file), $data);
        }
        $this->checkInfo('Addon boilerplate created successfully.');

        return $this;
    }

    /**
     * Run optional generators.
     *
     * @return $this
     */
    protected function generateOptional()
    {
        $optional = collect(['fieldtype', 'scope', 'modifier', 'tag', 'widget', 'action', 'filter'])
            ->filter(function ($type) {
                return $this->option($type) || $this->option('all');
            });

        if ($optional->isEmpty()) {
            return $this;
        }

        $this->output->newLine();
        $this->line('Generating additional addon components...');

        $optional->each(function ($type) {
            $this->runOptionalAddonGenerator($type);
        });

        $this->checkInfo('Additional components created successfully.');

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

        $this->info("Repository added to your application's composer.json successfully.");

        return $this;
    }

    /**
     * Install addon.
     *
     * @return $this
     */
    protected function installAddon()
    {
        $this->output->newLine();
        $this->line('Installing your addon with Composer. This may take a moment...');
        $this->addRepositoryPath();

        try {
            Composer::withoutQueue()->throwOnFailure()->require($this->package);
        } catch (ProcessException $exception) {
            $this->line($exception->getMessage());
            $this->output->newLine();
            throw new \Exception('An error was encountered while installing your addon!');
        }

        $this->checkInfo('Addon installed successfully.');

        return $this;
    }

    /**
     * Run optional addon generator command.
     *
     * @param  string  $type
     */
    protected function runOptionalAddonGenerator($type)
    {
        $prefix = $this->runningInPlease ? '' : 'statamic:';

        $name = studly_case($this->nameSlug);

        // Prevent conflicts when also creating a scope, since they're in the same directory.
        if ($type === 'filter') {
            $name .= 'Filter';
        }

        $arguments = ['name' => $name, 'addon' => $this->addonPath()];

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
     * @param  string|null  $file
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
