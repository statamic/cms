<?php

namespace Statamic\Marketplace;

use Facades\GuzzleHttp\Client as Guzzle;
use Illuminate\Cache\NoLock;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

class Client
{
    const LOCK_KEY = 'statamic.marketplace.lock';

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
     * @var Repository
     */
    private $store;

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

    public function get(string $endpoint, array $params = [])
    {
        return $this->request('GET', $endpoint, $params);
    }

    public function post(string $endpoint, array $params = [])
    {
        return $this->request('POST', $endpoint, $params);
    }

    /**
     * Send API request.
     *
     * @return mixed
     */
    private function request(string $method, string $endpoint, array $params = [])
    {
        $lock = $this->lock(static::LOCK_KEY, 10);

        $endpoint = collect([$this->domain, self::API_PREFIX, $endpoint])->implode('/');
        $key = 'marketplace-'.md5($endpoint.json_encode($params));

        try {
            $lock->block(5);

            return $this->cache()->rememberWithExpiration($key, function () use ($method, $endpoint, $params) {
                $response = Guzzle::request($method, $endpoint, [
                    'verify' => $this->verifySsl,
                    ($method === 'GET' ? 'query' : 'json') => $params,
                ]);

                $json = json_decode($response->getBody(), true);

                return [60 => $json];
            });
        } catch (LockTimeoutException $e) {
            return $this->cache()->get($key);
        } finally {
            $lock->release();
        }
    }

    private function cache(): Repository
    {
        return $this->store ??= Cache::store();
    }

    private function lock(string $key, int $seconds)
    {
        return $this->cache()->getStore() instanceof LockProvider
            ? $this->cache()->lock($key, $seconds)
            : new NoLock($key, $seconds);
    }
}
