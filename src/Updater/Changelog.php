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
        return $this->transformReleases(
            Marketplace::releases($this->item())['data']
        );
    }

    /**
     * Get paginated changelog.
     *
     * @return array
     */
    public function paginate($page = 1, $perPage = 10)
    {
        $response = Marketplace::releases($this->item(), [
            'page' => $page,
            'perPage' => $perPage,
        ]);

        return [
            'data' => $this->transformReleases($response['data'], $page),
            'meta' => $response['meta'],
        ];
    }

    /**
     * Transform releases into changelog format.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function transformReleases($releases, $page = 1)
    {
        $type = null;

        return $releases->map(function ($release, $index) use (&$type, $page) {
            $type = $type === 'downgrade' ? $type : $this->parseReleaseType($release['version'], $index);

            return (object) [
                'version' => $release['version'],
                'type' => $type,
                'latest' => $page === 1 && $index === 0,
                'licensed' => $this->isLicensed($release['version']),
                'date' => Carbon::parse($release['date'])->toIso8601String(),
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
