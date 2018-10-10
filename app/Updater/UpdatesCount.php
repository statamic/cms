<?php

namespace Statamic\Updater;

use Facades\Statamic\Composer\CoreUpdater;
use Illuminate\Support\Facades\Cache;
use Statamic\Statamic;

class UpdatesCount
{
    /**
     * @var int
     */
    public $count = 0;

    /**
     * Get updates count.
     *
     * @param bool $clearCache
     * @return int
     */
    public function get($clearCache = false)
    {
        if (Cache::has('updates-count') && ! $clearCache) {
            return Cache::get('updates-count');
        }

        return $this
            ->checkForStatamicUpdates()
            ->checkForAddonUpdates()
            ->cacheCount()
            ->getCount();
    }

    /**
     * Check for statamic updates and increment count.
     *
     * @return $this
     */
    protected function checkForStatamicUpdates()
    {
        if (Statamic::version() != CoreUpdater::latestVersion()) {
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
        //

        return $this;
    }

    /**
     * Cache count.
     *
     * @return $this
     */
    protected function cacheCount()
    {
        Cache::put('updates-count', $this->count, 60);

        return $this;
    }

    /**
     * Get count.
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }
}
