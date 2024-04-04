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
            ->migrateSystemConfig()
            ->ensureMultisiteConfigEnabled($sites)
            ->removeOldSitesConfigFile();

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

    private function migrateSystemConfig()
    {
        if (! File::exists($configPath = config_path('statamic/system.php'))) {
            File::copy(__DIR__.'/../../config/system.php', $configPath);

            return $this;
        }

        $config = File::get($configPath = config_path('statamic/system.php'));

        // Insert new `multisite` config...
        $config = str_replace(<<<'SEARCH'
    /*
    |--------------------------------------------------------------------------
    | Default Addons Paths
    |--------------------------------------------------------------------------
SEARCH, <<<'REPLACE'
    /*
    |--------------------------------------------------------------------------
    | Enable Multi-site
    |--------------------------------------------------------------------------
    |
    | Whether Statamic's multi-site functionality should be enabled. It is
    | assumed Statamic Pro is also enabled. To get started, you can run
    | the `php please multisite` command to update your content file
    | structure, after which you can manage your sites in the CP.
    |
    | https://statamic.dev/multi-site
    |
    */

    'multisite' => false,

    /*
    |--------------------------------------------------------------------------
    | Default Addons Paths
    |--------------------------------------------------------------------------
REPLACE, $config);

        // And if the above insertion didn't work, just append to bottom of config...
        if (! str_contains($config, 'multisite')) {
            $config = str_replace('];', <<<'REPLACE'
    /*
    |--------------------------------------------------------------------------
    | Enable Multi-site
    |--------------------------------------------------------------------------
    |
    | Whether Statamic's multi-site functionality should be enabled. It is
    | assumed Statamic Pro is also enabled. To get started, you can run
    | the `php please multisite` command to update your content file
    | structure, after which you can manage your sites in the CP.
    |
    | https://statamic.dev/multi-site
    |
    */

    'multisite' => false,

];
REPLACE, $config);
        }

        File::put($configPath, $config);

        return $this;
    }

    private function ensureMultisiteConfigEnabled($sites)
    {
        if (count($sites) <= 1) {
            return $this;
        }

        $config = File::get($configPath = config_path('statamic/system.php'));

        $config = str_replace("'multisite' => false", "'multisite' => true", $config);

        File::put($configPath, $config);

        return $this;
    }

    private function removeOldSitesConfigFile()
    {
        File::delete(config_path('statamic/sites.php'));

        return $this;
    }
}
