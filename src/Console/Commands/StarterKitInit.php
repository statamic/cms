<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\Commands\Concerns\MigratesLegacyStarterKitConfig;
use Statamic\Console\RunsInPlease;
use Statamic\Console\ValidatesInput;
use Statamic\Facades\File;
use Statamic\Rules\ComposerPackage;

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
        { --name : Specify a name for the starter kit }
        { --description : Specify a description of the starter kit }
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
        $package = $this->getKitPackage();
        $name = $this->getKitName();
        $description = $this->getKitDescription();

        if (! $package || ! $name || ! $description) {
            $this->components->info('You can manage your starter kit\'s package config in [package/composer.json] at any time.');
        }

        $this
            ->migrateLegacyConfig()
            ->createFolder()
            ->createConfig()
            ->createComposerJson($package, $name, $description);

        $this->components->success('Your starter kit config was successfully created in your project\'s [package] folder.');
    }

    /**
     * Get starter kit package for composer.json (optional).
     */
    protected function getKitPackage(bool $promptingAgain = false): ?string
    {
        $promptText = 'Starter Kit Package (eg. hasselhoff/kung-fury)';

        if ($promptingAgain) {
            $package = text($promptText);
        } else {
            $package = $this->argument('package') ?: text($promptText);
        }

        $fails = $this->validationFails($package, new ComposerPackage);

        if ($package && $fails && $this->input->isInteractive()) {
            return $this->getKitPackage(true);
        } elseif ($package && $fails) {
            return null;
        }

        return $package;
    }

    /**
     * Get starter kit name for composer.json (optional).
     */
    protected function getKitName(): ?string
    {
        return $this->option('name') ?: text('Starter Kit Name (eg. Kung Fury)');
    }

    /**
     * Get starter kit description for composer.json (optional).
     */
    protected function getKitDescription(): ?string
    {
        return $this->option('description') ?: text('Starter Kit Description');
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
    protected function createConfig(): self
    {
        if ($this->migratedLegacyConfig()) {
            return $this;
        }

        $contents = File::get(__DIR__.'/stubs/starter-kits/starter-kit.yaml.stub');

        $targetPath = base_path('package/starter-kit.yaml');

        if (File::exists($targetPath) && $this->input->isInteractive() && ! $this->option('force')) {
            if (! confirm('A [starter-kit.yaml] config already exists. Would you like to overwrite it?', false)) {
                return $this;
            }
        }

        // TODO: Ask if updatable, and if so prepend `updatable: true` to yaml and create service provider too?

        File::put($targetPath, $contents);

        return $this;
    }

    /**
     * Create composer.json config from stub.
     */
    protected function createComposerJson(?string $package, ?string $name, ?string $description): self
    {
        $contents = File::get(__DIR__.'/stubs/starter-kits/composer.json.stub');

        $targetPath = base_path('package/composer.json');

        if (File::exists($targetPath) && $this->input->isInteractive() && ! $this->option('force')) {
            if (! confirm('A [composer.json] config already exists. Would you like to overwrite it?', false)) {
                return $this;
            }
        }

        if ($package) {
            $contents = str_replace('example/starter-kit-package', $package, $contents);
        }

        if ($name) {
            $contents = str_replace('Example Name', $name, $contents);
        }

        if ($description) {
            $contents = str_replace('A description of your starter kit', $description, $contents);
        }

        File::put($targetPath, $contents);

        return $this;
    }
}
