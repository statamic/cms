<?php

namespace Statamic\Updater\Core;

use GuzzleHttp\Client;
use Statamic\Statamic;
use Illuminate\Support\Facades\Cache;
use Statamic\Updater\Changelog as BaseChangelog;

class Changelog extends BaseChangelog
{
    /**
     * @var int
     */
    const CACHE_FOR_MINUTES = 60;

    /**
     * @var string
     */
    protected $domain = 'https://statamic.com';

    /**
     * @var bool
     */
    protected $verifySsl = true;

    /**
     * Instantiate core changelog.
     *
     * @param string $slug
     */
    public function __construct(string $slug)
    {
        parent::__construct($slug);

        if ($domain = env('STATAMIC_DOMAIN')) {
            $this->domain = $domain;
            $this->verifySsl = false;
        }
    }

    /**
     * Get installed version.
     *
     * @return string
     */
    public function currentVersion()
    {
        return Statamic::version();
    }

    /**
     * Get composer package.
     *
     * @return string
     */
    public function composerPackage()
    {
        return Statamic::CORE_REPO;
    }

    /**
     * Get releases.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getReleases()
    {
        return Cache::remember("statamic/changelog", static::CACHE_FOR_MINUTES, function () {
            return collect(json_decode($this->queryApi()->getBody(), true)['data']);
        });
    }

    /**
     * Query API for statamic changelog.
     *
     * return mixed
     */
    protected function queryApi()
    {
        $client = new Client;

        return $client->get("{$this->domain}/api/v1/three/changelog", [
            'verify' => $this->verifySsl,
        ]);
    }
}
