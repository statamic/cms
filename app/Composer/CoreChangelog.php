<?php

namespace Statamic\Composer;

use Carbon\Carbon;
use Facades\Statamic\Composer\Composer;
use Facades\Statamic\Composer\CoreUpdater;
use GuzzleHttp\Client;
use Statamic\API\Str;
use Statamic\Statamic;

class CoreChangelog
{
    /**
     * @var string
     */
    public $currentVersion;

    /**
     * Instantiate core changelog.
     */
    public function __construct()
    {
        $this->currentVersion = Statamic::version();
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
                'version' => $release->tag_name,
                'type' => $this->parseReleaseType($release->tag_name, $index),
                'latest' => $index === 0,
                'date' => Carbon::parse($release->created_at)->format('F jS, Y'),
                'body' => $this->formatRelease($release->body),
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
     * Get releases.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getReleases()
    {
        $client = new Client;
        $response = $client->get('https://outpost.statamic.com/v2/changelog');

        return collect(json_decode($response->getBody()));
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

    /**
     * Format release.
     *
     * @param string $string
     * @return string
     */
    protected function formatRelease(string $string)
    {
        $string = markdown($string);

        // Barf!  Figure out if new changelog API will be outputting straight JSON before refactoring this...
        $string = Str::replace($string, '[new]', '<span class="label block text-center text-white rounded" style="background: #5bc0de; padding: 2px; padding-bottom: 1px;">NEW</span>');
        $string = Str::replace($string, '[fix]', '<span class="label block text-center text-white rounded" style="background: #5cb85c; padding: 2px; padding-bottom: 1px;">NEW</span>');
        $string = Str::replace($string, '[break]', '<span class="label block text-center text-white rounded" style="background: #d9534f; padding: 2px; padding-bottom: 1px;">NEW</span>');

        return $string;
    }
}
