<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\Commands\Concerns\MigratesLegacyStarterKitConfig;
use Statamic\Console\RunsInPlease;
use Statamic\Console\ValidatesInput;
use Statamic\Facades\File;
use Statamic\Rules\ComposerPackage;
use Statamic\StarterKits\Exceptions\StarterKitException;
use Statamic\Support\Arr;
use Statamic\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

class StarterKitInit extends Command
{
    use MigratesLegacyStarterKitConfig, RunsInPlease, ValidatesInput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:starter-kit:init
        { package? : Specify a package for the starter kit (ie. vendor/starter-kit) }
        { --name= : Specify a name for the starter kit }
        { --description= : Specify a description of the starter kit }
        { --updatable : Specify whether the starter kit is to be updatable }
        { --force : Force overwrite if files already exist }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new starter kit config';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $package = $this->getKitPackage();
            $name = $this->getKitName();
            $description = $this->getKitDescription();
            $updatable = $this->getKitUpdatable();
        } catch (StarterKitException $exception) {
            return 1;
        }

        if (! $package || ! $name || ! $description) {
            $this->components->info('You can manage your starter kit\'s package config in [package/composer.json] at any time.');
        }

        $this
            ->migrateLegacyConfig()
            ->createFolder()
            ->createConfig($updatable)
            ->createComposerJson($package, $name, $description, $updatable);

        $this->components->success('Your starter kit config was successfully created in your project\'s [package] folder.');
    }

    /**
     * Get starter kit package (optional).
     */
    protected function getKitPackage(bool $promptingAgain = false): ?string
    {
        $promptText = 'Starter Kit Package (eg. hasselhoff/kung-fury)';

        if ($promptingAgain) {
            $package = text($promptText);
        } elseif ($this->input->isInteractive()) {
            $package = $this->argument('package') ?: text($promptText);
        } else {
            $package = $this->argument('package');
        }

        if ($package) {
            $fails = $this->validationFails($package, new ComposerPackage);
        }

        if ($package && $fails && $this->input->isInteractive()) {
            return $this->getKitPackage(true);
        } elseif ($package && $fails) {
            throw new StarterKitException;
        }

        return $package;
    }

    /**
     * Get starter kit name (optional).
     */
    protected function getKitName(): ?string
    {
        if (! $this->input->isInteractive()) {
            return $this->option('name');
        }

        return $this->option('name') ?: text('Starter Kit Name (eg. Kung Fury)');
    }

    /**
     * Get starter kit description (optional).
     */
    protected function getKitDescription(): ?string
    {
        if (! $this->input->isInteractive()) {
            return $this->option('description');
        }

        return $this->option('description') ?: text('Starter Kit Description');
    }

    /**
     * Get whether the starter kit is to be updatable (optional).
     */
    protected function getKitUpdatable(): bool
    {
        if (! $this->input->isInteractive()) {
            return $this->option('updatable');
        }

        return $this->option('updatable') ?: confirm(
            label: 'Would you like to make this starter-kit updatable?',
            default: false,
            hint: 'Read more: https://statamic.dev/starter-kits/creating-a-starter-kit#making-starter-kits-updatable',
        );
    }

    /**
     * Create composer.json config from stub.
     */
    protected function createFolder(): self
    {
        if (! File::exists($dir = base_path('package'))) {
            File::makeDirectory($dir, 0755, true);
        }

        return $this;
    }

    /**
     * Create starter-kit.yaml config from stub.
     */
    protected function createConfig(bool $updatable): self
    {
        if ($this->migratedLegacyConfig()) {
            return $this;
        }

        $contents = File::get(__DIR__.'/stubs/starter-kits/starter-kit.yaml.stub');

        $targetPath = base_path('package/starter-kit.yaml');

        if ($this->input->isInteractive() && File::exists($targetPath) && ! $this->option('force')) {
            if (! confirm('A [starter-kit.yaml] config already exists. Would you like to overwrite it?', false)) {
                return $this;
            }
        }

        if ($updatable) {
            $contents = "updatable: true\n".$contents;
        }

        File::put($targetPath, $contents);

        return $this;
    }

    /**
     * Create composer.json config.
     */
    protected function createComposerJson(?string $package, ?string $name, ?string $description, bool $updatable): self
    {
        $targetPath = base_path('package/composer.json');

        if ($this->input->isInteractive() && File::exists($targetPath) && ! $this->option('force')) {
            if (! confirm('A [composer.json] config already exists. Would you like to overwrite it?', false)) {
                return $this;
            }
        }

        $json = [
            'name' => 'example/starter-kit-package',
            'extra' => [
                'statamic' => [
                    'name' => 'Example Name',
                    'description' => 'A description of your starter kit',
                ],
            ],
        ];

        if ($package) {
            Arr::set($json, 'name', $package);
        }

        if ($name) {
            Arr::set($json, 'extra.statamic.name', $name);
        }

        if ($description) {
            Arr::set($json, 'extra.statamic.description', $description);
        }

        if ($updatable && $namespace = static::createKitNamespace($package, $name)) {
            Arr::set($json, 'autoload.psr-4', [$namespace.'\\' => 'src']);
            Arr::set($json, 'autoload-dev.psr-4', ['Tests\\' => 'tests']);
            Arr::set($json, 'extra.laravel.providers', [$namespace.'\\ServiceProvider']);
        }

        File::put($targetPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $this;
    }

    /**
     * Create kit namespace from name and input if possible.
     */
    public static function createKitNamespace(?string $package, ?string $name): string
    {
        $vendor = 'Example';
        $namespace = 'StarterKitNamespace';

        if ($package) {
            [$vendor, $namespace] = explode('/', $package);
        }

        if ($name) {
            $namespace = $name;
        }

        return Str::upperCamelize($vendor).'\\'.Str::upperCamelize($namespace);
    }
}
