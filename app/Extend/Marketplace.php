<?php

namespace Statamic\Extend;

use Facades\App\Services\Composer;
use GuzzleHttp\Client;

class Marketplace
{
    const API_PREFIX = 'api/v1/marketplace';

    /**
     * @var string
     */
    protected $domain;

    /**
     * Instantiate marketplace API wrapper.
     */
    public function __construct()
    {
        $this->domain = env('STATAMIC_DOMAIN') ?? 'https://statamic.com';
    }

    /**
     * Get marketplace approved addons.
     */
    public function approvedAddons()
    {
        $client = new Client;
        $response = $client->get($this->api('addons'));
        $addons = json_decode($response->getBody());

        // Cache and return

        return $addons;
    }

    /**
     * Build api endpoint.
     *
     * @param string $endpoint
     */
    protected function api($endpoint)
    {
        return collect([$this->domain, self::API_PREFIX, $endpoint])->implode('/');
    }
}
