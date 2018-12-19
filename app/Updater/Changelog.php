<?php

namespace Statamic\Updater;

use Carbon\Carbon;
use Statamic\Statamic;
use Statamic\API\Addon;
use Facades\Statamic\Extend\Marketplace;
use Statamic\Updater\Core\Changelog as CoreChangelog;
use Statamic\Updater\Presenters\GithubReleasePresenter;

class Changelog
{
    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $currentVersion;

    /**
     * @var \Statamic\Extend\Addon
     */
    protected $installedAddon;

    /**
     * Instantiate product changelog.
     *
     * @param string $slug
     */
    public function __construct(string $slug)
    {
        $this->slug = $slug;
        $this->currentVersion = $this->currentVersion();
    }

    /**
     * Instantiate product changelog.
     *
     * @param string $slug
     * @return static
     */
    public static function product(string $slug)
    {
        if ($slug === Statamic::CORE_SLUG) {
            return new CoreChangelog($slug);
        }

        return new static($slug);
    }

    /**
     * Get installed version.
     *
     * @return string
     */
    public function currentVersion()
    {
        return $this->installedAddon()->version();
    }

    /**
     * Get composer package.
     *
     * @return string
     */
    public function composerPackage()
    {
        return $this->installedAddon()->package();
    }

    /**
     * Get changelog, sorted from newest to oldest.
     *
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        return $this->getReleases()->map(function ($release, $index) {
            return (object) [
                'version' => $release['version'],
                'type' => $this->parseReleaseType($release['version'], $index),
                'latest' => $index === 0,
                'date' => Carbon::parse($release['date'])->format('F jS, Y'),
                'body' => (string) new GithubReleasePresenter($release['changelog']),
            ];
        });
    }

    /**
     * Get latest release.
     *
     * @return \stdClass
     */
    public function latest()
    {
        return $this->get()->first();
    }

    /**
     * Get installed addon instance.
     *
     * @return \Statamic\Extend\Addon
     */
    protected function installedAddon()
    {
        return $this->installedAddon
            ?? $this->installedAddon = Addon::all()->first(function ($addon) {
                return $addon->marketplaceSlug() === $this->slug;
            });
    }

    /**
     * Get releases.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getReleases()
    {
        return collect(Marketplace::show($this->slug)['data']['variants'])
            ->pluck('releases')
            ->flatten(1);
    }

    /**
     * Parse release type.
     *
     * @param string $releaseVersion
     * @return string
     */
    protected function parseReleaseType($releaseVersion)
    {
        if (version_compare($releaseVersion, $this->currentVersion, '=')) {
            return 'current';
        } elseif (version_compare($releaseVersion, $this->currentVersion, '>')) {
            return 'upgrade';
        }

        return 'downgrade';
    }
}
