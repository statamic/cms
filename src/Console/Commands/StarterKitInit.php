<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Console\ValidatesInput;
use Statamic\Facades\File;
use Statamic\Rules\ComposerPackage;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

class StarterKitInit extends Command
{
    use RunsInPlease, ValidatesInput;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:starter-kit:init
        { name? : Specify a name for the starter kit }
        { description? : Specify a description of the starter kit }
        { package? : Specify a package for the starter kit (ie. vendor/starter-kit) }';

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
        $name = $this->getKitName();
        $description = $this->getKitDescription();
        $package = $this->getKitPackage();

        if (! $name || ! $package || ! $description) {
            $this->components->info('You can manage your starter kit\'s package config in [package/composer.json] at any time.');
        }

        $this
            ->createFolder()
            // ->migrateLegacy() // TODO: Consolidate logic to trait from other PR
            ->createConfig()
            ->createComposerJson($name, $description, $package);

        $this->components->success('Starter kit config was successfully created in [package/] folder.');
    }

    /**
     * Get starter kit name for composer.json (optional).
     */
    protected function getKitName(): ?string
    {
        return $this->argument('name') ?: text('Starter Kit Name');
    }

    /**
     * Get starter kit description for composer.json (optional).
     */
    protected function getKitDescription(): ?string
    {
        return $this->argument('description') ?: text('Starter Kit Description');
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
        $contents = File::get(__DIR__.'/stubs/starter-kits/starter-kit.yaml.stub');

        $targetPath = base_path('package/starter-kit.yaml');

        if (File::exists($targetPath) && $this->input->isInteractive()) {
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
    protected function createComposerJson(?string $name, ?string $description, ?string $package): self
    {
        $contents = File::get(__DIR__.'/stubs/starter-kits/composer.json.stub');

        $targetPath = base_path('package/composer.json');

        if (File::exists($targetPath) && $this->input->isInteractive()) {
            if (! confirm('A [composer.json] config already exists. Would you like to overwrite it?', false)) {
                return $this;
            }
        }

        if ($name) {
            $contents = str_replace('Example Name', $name, $contents);
        }

        if ($description) {
            $contents = str_replace('A description of your starter kit', $description, $contents);
        }

        if ($package) {
            $contents = str_replace('example/starter-kit-package', $package, $contents);
        }

        File::put($targetPath, $contents);

        return $this;
    }
}
