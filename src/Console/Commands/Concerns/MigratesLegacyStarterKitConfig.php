<?php

namespace Statamic\Console\Commands\Concerns;

use Statamic\Facades\File;

use function Laravel\Prompts\confirm;

trait MigratesLegacyStarterKitConfig
{
    protected $migrated = false;

    /**
     * Determine if dev sandbox has starter-kit.yaml at root and/or customized composer.json at target path.
     */
    protected function isUsingLegacyExporterConventions(): bool
    {
        return File::exists(base_path('starter-kit.yaml'));
    }

    /**
     * Determine if dev sandbox has starter-kit.yaml at root and/or customized composer.json at target path.
     */
    protected function migrateLegacyConfig(?string $exportPath = null): self
    {
        if (! $this->isUsingLegacyExporterConventions()) {
            return $this;
        }

        if ($this->input->isInteractive()) {
            if (! confirm('Config should now live in the [package] folder. Would you like Statamic to move it for you?', true)) {
                return $this;
            }
        }

        if (! File::exists($dir = base_path('package'))) {
            File::makeDirectory($dir, 0755, true);
        }

        if (File::exists($starterKitConfig = base_path('starter-kit.yaml'))) {
            File::move($starterKitConfig, base_path('package/starter-kit.yaml'));
            $this->components->info('Starter kit config moved to [package/starter-kit.yaml].');
        }

        if (File::exists($postInstallHook = base_path('StarterKitPostInstall.php'))) {
            File::move($postInstallHook, base_path('package/StarterKitPostInstall.php'));
            $this->components->info('Starter kit post-install hook moved to [package/StarterKitPostInstall.php].');
        }

        if ($exportPath && File::exists($packageComposerJson = $exportPath.'/composer.json')) {
            File::move($packageComposerJson, base_path('package/composer.json'));
            $this->components->info('Composer package config moved to [package/composer.json].');
        }

        $this->migrated = true;

        return $this;
    }

    /**
     * Check if migration logic was ran.
     */
    protected function migratedLegacyConfig(): bool
    {
        return $this->migrated;
    }
}
