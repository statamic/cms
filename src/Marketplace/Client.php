<?php

namespace Statamic\Marketplace;

use Facades\GuzzleHttp\Client as Guzzle;
use Illuminate\Cache\NoLock;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Illuminate\Contracts\Cache\Store;

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
     * @var Store
     */
    private $store;

    const LOCK_KEY = 'statamic.marketplace.lock';

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
        $lock = $this->lock(static::LOCK_KEY, 10);

        try {
            $lock->block(5);

            $endpoint = collect([$this->domain, self::API_PREFIX, $endpoint])->implode('/');

            $key = 'marketplace-'.md5($endpoint.json_encode($params));

            return $this->cache()->rememberWithExpiration($key, function () use ($endpoint, $params) {
                $response = Guzzle::request('GET', $endpoint, [
                    'verify' => $this->verifySsl,
                    'query' => $params,
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

    private function cache()
    {
        if ($this->store) {
            return $this->store;
        }

        try {
            $store = Cache::store('marketplace');
        } catch (InvalidArgumentException $e) {
            $store = Cache::store();
        }

        return $this->store = $store;
    }

    private function lock(string $key, int $seconds)
    {
        return $this->cache()->getStore() instanceof LockProvider
            ? $this->cache()->lock($key, $seconds)
            : new NoLock($key, $seconds);
    }
}
