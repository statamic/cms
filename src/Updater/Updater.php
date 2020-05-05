<?php

namespace Statamic\Updater;

use Facades\Statamic\Console\Processes\Composer;
use Statamic\Facades\Addon;
use Statamic\Statamic;
use Statamic\Updater\Core\Updater as CoreUpdater;

class Updater
{
    /**
     * @var string
     */
    protected $slug;

    /**
     * Instantiate product updater.
     *
     * @param string $slug
     */
    public function __construct(string $slug)
    {
        $this->slug = $slug;
    }

    /**
     * Instantiate product updater.
     *
     * @param string $slug
     * @return static
     */
    public static function product(string $slug)
    {
        if ($slug === Statamic::CORE_SLUG) {
            return new CoreUpdater($slug);
        }

        return new static($slug);
    }

    /**
     * Update core to latest constrained version.
     */
    public function update()
    {
        return Composer::update($this->getPackage());
    }

    /**
     * Update to latest version.
     */
    public function updateToLatest()
    {
        // It can take time to figure out the latest version below,
        // so here we preemptively clear the output cache for the composer ajax polling.
        Composer::clearCachedOutput($this->getPackage());

        return Composer::require($this->getPackage(), $this->latestVersion());
    }

    /**
     * Install explicit version.
     *
     * @param string $version
     */
    public function installExplicitVersion(string $version)
    {
        return Composer::require($this->getPackage(), $version);
    }

    /**
     * Get package.
     *
     * @return string
     */
    protected function getPackage()
    {
        return Addon::all()->first(function ($addon) {
            return $addon->marketplaceSlug() === $this->slug;
        })->package();
    }

    /**
     * Get latest version.
     */
    protected function latestVersion()
    {
        return Changelog::product($this->slug)->latest()->version;
    }
}
