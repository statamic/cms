<?php

namespace Statamic\Composer;

use Carbon\Carbon;
use Facades\Statamic\Composer\Composer;
use Facades\Statamic\Composer\CoreUpdater;
use GuzzleHttp\Client;
use Statamic\API\Str;

class CoreChangelog
{
    /**
     * Get changelog, sorted from newest to oldest.
     *
     * @return \Illuminate\Support\Collection
     */
    public function get($currentVersion = null)
    {
        return $this->getReleases()->map(function ($release, $index) use ($currentVersion) {
            return (object) [
                'version' => $release->tag_name,
                'type' => $this->parseReleaseType($release->tag_name, $currentVersion, $index),
                'latest' => $index === 0,
                'date' => Carbon::parse($release->created_at)->format('F jS, Y'),
                'body' => $this->formatRelease($release->body),
            ];
        });
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
     * @param string $currentVersion
     * @return string
     */
    protected function parseReleaseType($releaseVersion, $currentVersion)
    {
        if (version_compare($releaseVersion, $currentVersion, '>')) {
            return 'upgrade';
        } elseif (version_compare($releaseVersion, $currentVersion, '=')) {
            return 'current';
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
        $string = Str::replace($string, '[new]', '<span class="label label-info">New</span>');
        $string = Str::replace($string, '[fix]', '<span class="label label-success">Fix</span>');
        $string = Str::replace($string, '[break]', '<span class="label label-danger">Break</span>');

        return $string;
    }
}
