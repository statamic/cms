<?php

namespace Statamic\Updater;

use Carbon\Carbon;
use Facades\Statamic\Marketplace\Marketplace;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Addon;
use Statamic\Statamic;
use Statamic\Support\Str;

class UpdatesOverview
{
    const CACHE_FOR_MINUTES = 60;

    protected $count;
    protected $statamic;
    protected $addons;

    /**
     * Get updates count.
     *
     * @return int
     */
    public function count()
    {
        return $this->getCached('updates-overview.count');
    }

    /**
     * Check if statamic update is available.
     *
     * @return bool
     */
    public function hasStatamicUpdate()
    {
        return $this->getCached('updates-overview.statamic');
    }

    /**
     * List updatable addons.
     *
     * @return array
     */
    public function updatableAddons()
    {
        return $this->getCached('updates-overview.addons');
    }

    /**
     * Get value from cache.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getCached($key)
    {
        if (! Cache::has($key)) {
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
        $version = Statamic::version();

        if (Str::startsWith($version, 'dev-') || Str::endsWith($version, '-dev')) {
            return $this;
        }

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
        $this->addons = Addon::all()
            ->reject->isLatestVersion()
            ->map->id()
            ->values()
            ->all();

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
