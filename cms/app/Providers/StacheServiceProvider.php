<?php

namespace Statamic\Providers;

use Exception;
use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\Config;
use Illuminate\Http\Request;
use Statamic\Stache\Loader;
use Statamic\Stache\Stache;
use Statamic\Stache\Manager;
use Illuminate\Support\ServiceProvider;
use Statamic\Stache\Persister;
use Statamic\Stache\UpdateManager;
use Statamic\Stache\EmptyStacheException;
use Statamic\Testing\Doubles\StacheTestManager;

class StacheServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * @var Stache
     */
    private $stache;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var Request
     */
    private $request;

    /**
     * Register services
     *
     * @return void
     */
    public function register()
    {
        $this->registerStache();

        $this->registerManager();
    }

    /**
     * Register the Stache
     *
     * @return void
     */
    private function registerStache()
    {
        $this->stache = $this->app->make(Stache::class);

        $this->app->singleton(Stache::class, function () {
            return $this->stache;
        });

        $this->app->alias(Stache::class, 'stache');
    }

    /**
     * Register the Stache Manager
     *
     * @return void
     */
    private function registerManager()
    {
        $class = (app()->environment() === 'testing') ? StacheTestManager::class : Manager::class;

        $manager = new $class(
            $this->stache,
            new Loader($this->stache),
            new UpdateManager($this->stache),
            new Persister($this->stache)
        );

        $this->app->instance(Manager::class, $manager);
    }

    /**
     * Load the Stache
     *
     * @param \Illuminate\Http\Request $request
     */
    public function boot(Request $request)
    {
        $this->cleanUpForConsole();

        $this->request = $request;

        $this->app->make(Stache::class)->locales(Config::getLocales());

        // On large sites, the Stache may take some time to build initially. If another request
        // hits Statamic while it's in the middle of being built, it may use a half-created
        // cache resulting in missing data. Here, we'll exit early with a simple refresh
        // meta tag. Once the Stache is built, the page will resume loading as usual.
        if ($this->stache->isPerformingInitialWarmUp() && !app()->runningInConsole()) {
            $this->outputRefreshResponse();
        }

        $this->manager = $this->app->make(Manager::class);

        $this->manager->registerDrivers();

        // If the config changed since the last request, we want to clear the Stache. This
        // includes routes and settings files. Changes here may affect how URIs and other
        // related values are calculated. It's better to just start from an empty slate.
        $this->clearOnConfigChange();

        // Should we update the stache?
        // This variable would be true or false based on a user setting whether
        // we should update on each request, or whether it's a glide route.
        $update = $this->shouldUpdateStache();

        try {
            // At this point the Stache is just an empty object. We'll want to load
            // (aka. 'hydrate') it. We'll also mark it as warmed. If it turns
            // out that it was empty/cold, the exception will adjust that.
            $this->manager->load();
            $this->stache->heat();
        } catch (EmptyStacheException $e) {
            // If the stache was empty, we need to be sure to update it. We also
            // want to mark it as "cold". Some services (like search indexing)
            // should not run on the initial warm up to prevent overloading.
            $update = true;
            $this->stache->cool();
        }

        // If we've opted to update the Stache, we'll do so, and
        // then persist any updates so we can load it next time.
        if ($update) {
            $this->updateStache();
        }

        $this->stache->heat();
    }

    /**
     * Update the Stache.
     *
     * If an error is encountered, the temporary file that keeps track of the
     * initial warm up should be deleted. Otherwise, users will run into an
     * infinitely refreshing/redirecting site. That's not very fun at all.
     *
     * @throws Exception
     */
    private function updateStache()
    {
        try {
            $this->manager->update();
        } catch (Exception $e) {
            if (File::exists($this->stache->building_path)) {
                File::delete($this->stache->building_path);
            }

            throw $e;
        }
    }

    /**
     * Should the Stache get updated?
     *
     * @return bool
     */
    private function shouldUpdateStache()
    {
        // Always-updating settings is off? Short-circuit here. Don't update.
        if (! Config::get('caching.stache_always_update')) {
            return false;
        }

        // Is this a Glide route? We don't want to update for those.
        $glide_route = ltrim(Str::ensureRight(Config::get('assets.image_manipulation_route'), '/'), '/');
        if (Str::startsWith($this->request->path(), $glide_route)) {
            return false;
        }

        // Got this far? We'll update.
        return true;
    }

    /**
     * If the config has changed since last time, we want to clear the Stache.
     *
     * @return void
     */
    private function clearOnConfigChange()
    {
        if ($this->manager->hasConfigChanged()) {
            \Statamic\API\Stache::clear();
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Stache::class];
    }

    /**
     * When the Stache is being built, we'll output a refresh/redirect until it's ready.
     *
     * @return void
     */
    private function outputRefreshResponse()
    {
        $html = sprintf('<meta http-equiv="refresh" content="1; URL=\'%s\'" />', request()->getUri());

        exit($html);
    }

    private function cleanUpForConsole()
    {
        if (! app()->runningInConsole()) {
            return;
        }

        if (File::exists($lock = $this->stache->building_path)) {
            File::delete($lock);
        }

        Config::set('system.ensure_unique_ids', false);
    }
}
