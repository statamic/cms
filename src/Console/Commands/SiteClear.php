<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\YAML;

class SiteClear extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:site:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a fresh site, wiping away all content';

    /**
     * Filesystem.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->shouldAbort()) {
            return $this->info('Aborted successfully.');
        }

        $this->files = app(Filesystem::class);

        $this
            ->clearCollections()
            ->clearNavigations()
            ->clearTrees()
            ->clearTaxonomies()
            ->clearAssets()
            ->clearGlobals()
            ->clearForms()
            ->clearUsers()
            ->clearGroups()
            ->clearRoles()
            ->clearBlueprints()
            ->clearFieldsets()
            ->clearViews()
            ->resetStatamicConfigs()
            ->clearCache();
    }

    /**
     * Check if command should be aborted.
     *
     * @return bool
     */
    protected function shouldAbort()
    {
        if ($this->option('no-interaction')) {
            return false;
        }

        return ! $this->confirm('There is no site theme or sample content in v3 - are you sure you want to remove all new site defaults?', false);
    }

    /**
     * Clear all collections.
     *
     * @return $this
     */
    protected function clearCollections()
    {
        $this->cleanAndKeep(base_path('content/collections'));

        $this->info('Collections cleared successfully.');

        return $this;
    }

    /**
     * Clear all structures.
     *
     * @return $this
     */
    protected function clearNavigations()
    {
        $this->cleanAndKeep(base_path('content/navigation'));

        $this->info('Navigations cleared successfully.');

        return $this;
    }

    /**
     * Clear all trees.
     *
     * @return $this
     */
    protected function clearTrees()
    {
        $this->cleanAndKeep(base_path('content/trees'));

        $this->info('Trees cleared successfully.');

        return $this;
    }

    /**
     * Clear all taxonomies.
     *
     * @return $this
     */
    protected function clearTaxonomies()
    {
        $this->cleanAndKeep(base_path('content/taxonomies'));

        $this->info('Taxonomies cleared successfully.');

        return $this;
    }

    /**
     * Clear all assets.
     *
     * @return $this
     */
    protected function clearAssets()
    {
        $path = base_path('content/assets');

        if ($this->files->exists($path)) {
            collect($this->files->files($path))->each(function ($container) {
                $this->removeAssetContainerDisk($container);
            });
        }

        $this->cleanAndKeep($path);

        $this->info('Assets cleared successfully.');

        return $this;
    }

    /**
     * Remove asset container disk.
     */
    protected function removeAssetContainerDisk($container)
    {
        if ($container->getExtension() !== 'yaml') {
            return;
        }

        if (! $disk = YAML::parse($container->getContents())['disk'] ?? false) {
            return;
        }

        // Don't remove any of the default disks.
        if (in_array($disk, ['local', 'public', 's3', 'assets'])) {
            return;
        }

        // TODO: Maybe we can eventually bring in and extract this to statamic/migrator's Configurator class...
        $filesystemsPath = config_path('filesystems.php');
        $config = $this->files->get($filesystemsPath);
        $diskRegex = "/\s{8}['\"]{$disk}['\"]\X*\s{8}\],?+\n\n?+/mU";
        $updatedConfig = preg_replace($diskRegex, '', $config);

        $this->files->put($filesystemsPath, $updatedConfig);

        if (config("filesystems.disks.{$disk}.driver") === 'local') {
            $this->files->deleteDirectory(config("filesystems.disks.{$disk}.root"));
        }
    }

    /**
     * Clear all globals.
     *
     * @return $this
     */
    protected function clearGlobals()
    {
        $this->cleanAndKeep(base_path('content/globals'));

        $this->info('Globals cleared successfully.');

        return $this;
    }

    /**
     * Clear all forms and submissions.
     *
     * @return $this
     */
    protected function clearForms()
    {
        $this->files->deleteDirectory(resource_path('forms'));
        $this->files->deleteDirectory(storage_path('forms'));

        $this->info('Forms cleared successfully.');

        return $this;
    }

    /**
     * Clear all users.
     *
     * @return $this
     */
    protected function clearUsers()
    {
        $this->cleanAndKeep(base_path('users'));

        $this->info('Users cleared successfully.');

        return $this;
    }

    /**
     * Clear all user groups.
     *
     * @return $this
     */
    protected function clearGroups()
    {
        $this->files->put($this->preparePath(resource_path('users/groups.yaml')), <<<EOT
# admin:
#   title: Administrators
#   roles:
#     - admin\n
EOT
        );

        $this->info('User groups cleared successfully.');

        return $this;
    }

    /**
     * Clear all user roles.
     *
     * @return $this
     */
    protected function clearRoles()
    {
        $this->files->put($this->preparePath(resource_path('users/roles.yaml')), <<<EOT
# admin:
#   title: Administrator
#   permissions:
#     - super\n
EOT
        );

        $this->info('User roles cleared successfully.');

        return $this;
    }

    /**
     * Clear all blueprints.
     *
     * @return $this
     */
    protected function clearBlueprints()
    {
        $this->cleanAndKeep(resource_path('blueprints'));

        $this->info('Blueprints cleared successfully.');

        return $this;
    }

    /**
     * Clear all fieldsets.
     *
     * @return $this
     */
    protected function clearFieldsets()
    {
        $this->cleanAndKeep(resource_path('fieldsets'));

        $this->info('Fieldsets cleared successfully.');

        return $this;
    }

    /**
     * Clear all views.
     *
     * @return $this
     */
    protected function clearViews()
    {
        $this->cleanAndKeep(resource_path('views'));

        $this->info('Views cleared successfully.');

        return $this;
    }

    /**
     * Reset statamic configs to defaults.
     *
     * @return $this
     */
    protected function resetStatamicConfigs()
    {
        $this->files->cleanDirectory(config_path('statamic'));

        $this->files->copyDirectory(__DIR__.'/../../../config', config_path('statamic'));
        $this->files->copy(__DIR__.'/stubs/config/stache.php.stub', config_path('statamic/stache.php'));
        $this->files->copy(__DIR__.'/stubs/config/users.php.stub', config_path('statamic/users.php'));

        $this->info('Statamic configs reset successfully.');

        return $this;
    }

    /**
     * Clear cache.
     *
     * @return $this
     */
    protected function clearCache()
    {
        $this->callSilent('cache:clear');

        return $this;
    }

    /**
     * Clean directory and add .gitkeep file.
     *
     * @param  string  $path
     */
    protected function cleanAndKeep($path)
    {
        if (! $this->files->exists($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }

        $this->files->cleanDirectory($path);

        $this->files->put("{$path}/.gitkeep", '');
    }

    /**
     * Prepare path directory.
     *
     * @param  string  $path
     * @return string
     */
    protected function preparePath($path)
    {
        $folder = preg_replace('/(.*)\/[^\/]+\.[^\/]+/', '$1', $path);

        if (! $this->files->exists($folder)) {
            $this->files->makeDirectory($folder, 0755, true);
        }

        return $path;
    }
}
