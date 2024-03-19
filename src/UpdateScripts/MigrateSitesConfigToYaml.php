<?php

namespace Statamic\UpdateScripts;

use Statamic\Facades\File;
use Statamic\Facades\Site;
use Statamic\Support\Arr;

class MigrateSitesConfigToYaml extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('5.0.0');
    }

    public function update()
    {
        // Skip if already migrated
        if (File::exists(base_path('content/sites.yaml'))) {
            return;
        }

        // Skip if no local sites config file exists
        if (! File::exists($configPath = config_path('statamic/sites.php'))) {
            return;
        }

        // They'll need to manually migrate if there was a problem getting their sites config
        if (! $sites = $this->migrateSites($configPath)) {
            return $this->outputMigrationError();
        }

        $this
            ->saveMigratedSitesToYaml($sites)
            ->copyNewSitesConfig($configPath);

        $this->console->success('Successfully migrated [config/statamic/sites.php] to [content/sites.yaml]!');
    }

    private function migrateSites($path)
    {
        // Get config file contents
        $config = File::get($path);

        // Replace func calls
        $config = $this->replaceConfigFuncCalls($config);
        $config = $this->replaceWhitelistedEnvFuncCalls($config);

        // Save updated config to a tmp file so we can easily require it to get actual array value
        File::put($tmpPath = $path.'.tmp', $config);

        // Require to get returned config array
        $migratedConfig = require $tmpPath;

        // Delete tmp file
        File::delete($tmpPath);

        // Just return `sites` config
        return Arr::get($migratedConfig, 'sites');
    }

    private function replaceConfigFuncCalls(string $config): string
    {
        // Convert all `config()` calls to `{{ config:... }}` antlers syntax
        $config = preg_replace('/config\([\'"]([^\'"]+)[\'"]\)/', '\'{{ config:$1 }}\'', $config);

        // Ensure `:` array syntax for deeper nested values
        while (preg_match($dotPattern = '/(config:\S*)(\.)/', $config)) {
            $config = preg_replace($dotPattern, '$1:', $config);
        }

        return $config;
    }

    private function replaceWhitelistedEnvFuncCalls(string $config): string
    {
        // Convert `env('APP_NAME')` references
        $config = preg_replace('/env\([\'"]APP_NAME[\'"]\)/', '\'{{ config:app:name }}\'', $config);

        // Convert `env('APP_URL')` references
        $config = preg_replace('/env\([\'"]APP_URL[\'"]\)/', '\'{{ config:app:url }}\'', $config);

        return $config;
    }

    private function outputMigrationError()
    {
        // TODO: Add helpful error output, instructing them how to manually migrate their sites config
    }

    private function saveMigratedSitesToYaml($sites)
    {
        Site::setSites($sites)->save();

        return $this;
    }

    private function copyNewSitesConfig($configPath)
    {
        $newConfig = File::get(__DIR__.'/../../config/sites.php');

        // If more than one site is configured, automatically enable multisite
        if (Site::all()->count() > 1) {
            $newConfig = str_replace("'enabled' => false", "'enabled' => true", $newConfig);
        }

        File::put($configPath, $newConfig);

        return $this;
    }
}
