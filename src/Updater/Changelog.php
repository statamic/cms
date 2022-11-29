<?php

namespace Statamic\Updater;

use Carbon\Carbon;
use Facades\Statamic\Marketplace\Marketplace;

abstract class Changelog
{
    /**
     * Get installed version.
     *
     * @return string
     */
    abstract public function currentVersion();

    /**
     * Get the marketplace item (seller slug + product slug).
     *
     * @return string
     */
    abstract public function item();

    /**
     * Get changelog, sorted from newest to oldest.
     *
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        $type = null;

        return Marketplace::releases($this->item())->map(function ($release, $index) use (&$type) {
            $type = $type === 'downgrade' ? $type : $this->parseReleaseType($release['version'], $index);

            return (object) [
                'version' => $release['version'],
                'type' => $type,
                'latest' => $index === 0,
                'licensed' => $this->isLicensed($release['version']),
                'date' => Carbon::parse($release['date'])->format(config('statamic.cp.date_format')),
                'body' => $release['changelog'],
            ];
        });
    }

    /**
     * Get available updates count.
     *
     * @return int
     */
    public function availableUpdatesCount()
    {
        return $this->get()->filter(function ($release) {
            return $release->type === 'upgrade';
        })->count();
    }

    /**
     * Get latest release.
     *
     * @return \stdClass
     */
    public function latest()
    {
        return optional($this->get()->first());
    }

    /**
     * Parse release type.
     *
     * @param  string  $releaseVersion
     * @return string
     */
    protected function parseReleaseType($releaseVersion)
    {
        if (version_compare($releaseVersion, $this->currentVersion(), '=')) {
            return 'current';
        } elseif (version_compare($releaseVersion, $this->currentVersion(), '>')) {
            return 'upgrade';
        }

        return 'downgrade';
    }

    protected function isLicensed($version)
    {
        return true;
    }
}
