<?php

namespace Statamic\Updater;

use Carbon\Carbon;
use Facades\Statamic\Marketplace\Marketplace;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Addon;

class UpdatesOverview
{
    const CACHE_FOR_MINUTES = 60;

    protected $count;
    protected $statamic;
    protected $addons;

    /**
     * Get updates count.
     *
     * @param  bool  $clearCache
     * @return int
     */
    public function count($clearCache = false)
    {
        return $this->getCached('updates-overview.count', $clearCache);
    }

    /**
     * Check if statamic update is available.
     *
     * @param  bool  $clearCache
     * @return bool
     */
    public function hasStatamicUpdate($clearCache = false)
    {
        return $this->getCached('updates-overview.statamic', $clearCache);
    }

    /**
     * List updatable addons.
     *
     * @param  bool  $clearCache
     * @return array
     */
    public function updatableAddons($clearCache = false)
    {
        return $this->getCached('updates-overview.addons', $clearCache);
    }

    /**
     * Get value from cache.
     *
     * @param  string  $key
     * @param  bool  $clearCache
     * @return mixed
     */
    public function getCached($key, $clearCache = false)
    {
        if (! Cache::has($key) || $clearCache) {
            $this->checkAndCache();
        }

        return Cache::get($key);
    }

    /**
     * Check for updates and cache results.
     *
     * @return $this
     */
    protected function checkAndCache()
    {
        return $this
            ->resetState()
            ->checkForStatamicUpdates()
            ->checkForAddonUpdates()
            ->cache();
    }

    /**
     * Reset state.
     */
    protected function resetState()
    {
        $this->count = 0;
        $this->statamic = false;
        $this->addons = [];

        return $this;
    }

    /**
     * Check for statamic updates and increment count.
     *
     * @return $this
     */
    protected function checkForStatamicUpdates()
    {
        if (Marketplace::statamic()->changelog()->latest()->type === 'upgrade') {
            $this->statamic = true;
            $this->count++;
        }

        return $this;
    }

    /**
     * Check for addon updates and increment count.
     *
     * @return $this
     */
    protected function checkForAddonUpdates()
    {
        Addon::all()
            ->reject->isLatestVersion()
            ->each(function ($addon) {
                $this->addons[$addon->marketplaceSlug()] = $addon->name();
                $this->count++;
            });

        return $this;
    }

    /**
     * Cache data.
     *
     * @return $this
     */
    protected function cache()
    {
        $expiry = Carbon::now()->addMinutes(self::CACHE_FOR_MINUTES);

        Cache::put('updates-overview.count', $this->count, $expiry);
        Cache::put('updates-overview.statamic', $this->statamic, $expiry);
        Cache::put('updates-overview.addons', $this->addons, $expiry);

        return $this;
    }
}
