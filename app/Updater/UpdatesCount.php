<?php

namespace Statamic\Updater;

use Statamic\Statamic;
use Statamic\API\Addon;
use Illuminate\Support\Facades\Cache;

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
        if (! $latest = Changelog::product(Statamic::CORE_SLUG)->latest()) {
            return $this;
        }

        if (Statamic::version() != $latest->version) {
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
            ->filter
            ->marketplaceSlug()
            ->filter(function ($addon) {
                return Changelog::product($addon->marketplaceSlug())->latest()->type === 'upgrade';
            })
            ->each(function () {
                $this->count++;
            });

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
