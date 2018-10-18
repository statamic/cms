<?php

namespace Statamic\Extend;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Statamic\API\Addon as AddonAPI;

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
     * Get addons.
     *
     * @return mixed
     */
    public function get()
    {
        return Cache::remember('marketplace-addons', $this->cacheForMinutes, function () {
            $payload = $this->apiRequest('addons');
            $payload = $this->addLocalMetaToPayload($payload);
            $payload = $this->addLocalDevelopmentAddonsToPayload($payload);

            return $payload;
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
        return collect($this->get()['data'])->first(function ($addon) use ($githubRepo) {
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
    protected function apiRequest($endpoint, $method = 'GET')
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
            'installed' => AddonAPI::all()->keys()->contains($addon['variants'][0]['githubRepo']),
        ]);
    }

    /**
     * Add local development addons to payload.
     *
     * @param array $payload
     * @return array
     */
    protected function addLocalDevelopmentAddonsToPayload($payload)
    {
        AddonAPI::all()->reject->marketplaceProductId()->each(function ($addon) use (&$payload) {
            $payload['data'][] = $this->buildAddonPayloadFromLocalData($addon);
        });

        return $payload;
    }

    /**
     * Build addon payload from local data.
     *
     * @param Addon $addon
     * @return array
     */
    protected function buildAddonPayloadFromLocalData(Addon $addon)
    {
        return [
            'id' => $addon->id(),
            'name' => $addon->name(),
            'variants' => [
                [
                    'id' => $addon->id() . '-variant',
                    'number' => 1,
                    'description' => 'N/A',
                    'assets' => [],
                ]
            ],
            'seller' => [
                'id' => $addon->id() . '-seller',
                'name' => 'NA',
                'website' => null,
                'avatar' => null,
            ],
            'installed' => true,
        ];
    }
}
