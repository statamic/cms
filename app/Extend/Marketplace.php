<?php

namespace Statamic\Extend;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Statamic\API\Addon;

class Marketplace
{
    const API_PREFIX = 'api/v1/marketplace';

    /**
     * @var string
     */
    protected $domain = 'https://statamic.com';

    /**
     * @var int
     */
    protected $cacheForMinutes = 60;

    /**
     * @var bool
     */
    protected $verifySsl = true;

    /**
     * Instantiate marketplace API wrapper.
     */
    public function __construct()
    {
        if ($domain = env('STATAMIC_DOMAIN')) {
            $this->domain = $domain;
            $this->cacheForMinutes = 0;
            $this->verifySsl = false;
        }
    }

    /**
     * Get marketplace approved addons.
     *
     * @return mixed
     */
    public function approvedAddons()
    {
        return Cache::remember('marketplace-approved-addons', $this->cacheForMinutes, function () {
            return $this->addLocalMetaToPayload($this->request('addons'));
        });
    }

    /**
     * Find addon by github repo.
     *
     * @param string $githubRepo
     * @return mixed
     */
    public function findByGithubRepo($githubRepo)
    {
        return collect($this->approvedAddons()['data'])->first(function ($addon) use ($githubRepo) {
            return data_get($addon, 'variants.0.githubRepo') === $githubRepo;
        });
    }

    /**
     * Send API request.
     *
     * @param string $endpoint
     * @param string $method
     * @return mixed
     */
    protected function request($endpoint, $method = 'GET')
    {
        $client = new Client;

        $response = $client->request($method, $this->buildEndpoint($endpoint), [
            'verify' => $this->verifySsl,
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Build api endpoint.
     *
     * @param string $uri
     * @return string
     */
    protected function buildEndpoint($endpoint)
    {
        return collect([$this->domain, self::API_PREFIX, $endpoint])->implode('/');
    }

    /**
     * Add local meta to whole payload.
     *
     * @param mixed $payload
     * @return array
     */
    protected function addLocalMetaToPayload($payload)
    {
        $payload['data'] = collect($payload['data'])->map(function ($addon) {
            return $this->addLocalMetaToAddon($addon);
        });

        return $payload;
    }

    /**
     * Add local meta to addon paylod.
     *
     * @param array $addon
     * @return array
     */
    protected function addLocalMetaToAddon($addon)
    {
        return array_merge($addon, [
            'installed' => Addon::all()->keys()->contains($addon['variants'][0]['githubRepo']),
        ]);
    }
}
