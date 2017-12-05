<?php

namespace Statamic\Providers;

use Statamic\API\Folder;
use Illuminate\Support\ServiceProvider;
use Statamic\Extend\Management\AddonManager;
use Statamic\Extend\Management\AddonRepository;
use Statamic\Extend\Management\ComposerManager;

class AddonServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    private $translated = [];

    /**
     * @var AddonRepository
     */
    private $repo;

    public function boot()
    {
        $this->loadTranslations();
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->repo = new AddonRepository($this->findAddonFiles());

        $this->app->instance(AddonRepository::class, $this->repo);

        $this->app->singleton(AddonManager::class, function () {
            return new AddonManager(
                app(ComposerManager::class),
                app(AddonRepository::class)
            );
        });

        $this->app->singleton('Statamic\Extend\Contextual\Store');

        $this->registerAddonServiceProviders();
    }

    private function registerAddonServiceProviders()
    {
        foreach ($this->repo->serviceProviders()->installed()->classes() as $provider) {
            $provider = $this->app->resolveProviderClass($provider);

            if (! empty($provider->providers)) {
                call_user_func([$provider, 'registerAdditionalProviders']);
            }

            if (! empty($provider->aliases)) {
                call_user_func([$provider, 'registerAdditionalAliases']);
            }

            $this->app->register($provider);
        }
    }

    private function findAddonFiles()
    {
        $files = [];

        foreach ([addons_path(), bundles_path()] as $path) {
            foreach (Folder::getFolders($path) as $addonFolder) {
                $files = array_merge($files, Folder::getFilesRecursivelyExcept($addonFolder, ['node_modules', 'vendor']));
            }
        }

        return collect_files($files)->removeHidden();
    }

    private function loadTranslations()
    {
        $files = $this->repo->files()->filter(function ($path) {
            return preg_match('/resources\/lang/', $path);
        });

        $files->each(function ($path) {
            $parts = explode('/', $path);

            $addon = $parts[2];

            // move on if we've already added this addon
            if (in_array($addon, $this->translated)) {
                return true;
            }

            $namespace = 'addons.'.$addon;

            $parts = array_slice($parts, 0, 5);
            $path = join('/', $parts);

            $this->loadTranslationsFrom(root_path($path), $namespace);
        });
    }
}
