<?php

namespace Statamic\Marketplace;

use Facades\GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Facades\Cache;

class Client
{
    /**
     * @var string
     */
    const API_PREFIX = 'api/v1/marketplace';

    /**
     * @var string
     */
    protected $domain = 'https://statamic.com';

    /**
     * @var bool
     */
    protected $verifySsl = true;

    /**
     * @var int
     */
    protected $cache;

    /**
     * Instantiate marketplace API wrapper.
     */
    public function __construct()
    {
        if ($domain = env('STATAMIC_DOMAIN')) {
            $this->domain = $domain;
            $this->verifySsl = false;
        }
    }

    /**
     * Send API request.
     *
     * @param  string  $endpoint
     * @param  arra  $params
     * @return mixed
     */
    public function get($endpoint, $params = [])
    {
        $endpoint = collect([$this->domain, self::API_PREFIX, $endpoint])->implode('/');

        $key = 'marketplace-'.md5($endpoint.json_encode($params));

        return Cache::rememberWithExpiration($key, function () use ($endpoint, $params) {
            $response = Guzzle::request('GET', $endpoint, [
                'verify' => $this->verifySsl,
                'query' => $params,
            ]);

            $json = json_decode($response->getBody(), true);

            return [$this->cache => $json];
        });
    }

    public function cache($cache = 60)
    {
        $this->cache = $cache;

        return $this;
    }
}
