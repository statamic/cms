<?php

namespace Statamic\Licensing;

use Carbon\CarbonInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Statamic\Facades;
use Statamic\Statamic;
use Statamic\Support\Arr;

class Outpost
{
    const ENDPOINT = 'https://outpost.statamic.com/v3/query';
    const REQUEST_TIMEOUT = 5;
    const CACHE_KEY = 'statamic.outpost.response';
    const LOCK_KEY = 'statamic.outpost.lock';

    private $response;
    private $client;
    private $store;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function radio()
    {
        $this->response();
    }

    public function response()
    {
        return $this->response ?? $this->request();
    }

    private function request()
    {
        $lock = $this->cache()->lock(static::LOCK_KEY, 10);

        try {
            $lock->block(static::REQUEST_TIMEOUT);

            if ($this->hasCachedResponse()) {
                return $this->getCachedResponse();
            }

            return $this->performAndCacheRequest();
        } catch (ConnectException $e) {
            return $this->cacheAndReturnErrorResponse($e);
        } catch (RequestException $e) {
            return $this->handleRequestException($e);
        } catch (LockTimeoutException $e) {
            return $this->cacheAndReturnErrorResponse($e);
        } finally {
            $lock->release();
        }
    }

    private function performAndCacheRequest()
    {
        return $this->cacheResponse(now()->addHour(), $this->performRequest());
    }

    private function performRequest()
    {
        $response = $this->client->request('POST', self::ENDPOINT, [
            'headers' => ['accept' => 'application/json'],
            'json' => $this->payload(),
            'timeout' => self::REQUEST_TIMEOUT,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function payload()
    {
        return [
            'key' => config('statamic.system.license_key'),
            'host' => request()->getHost(),
            'ip' => request()->server('SERVER_ADDR'),
            'port' => request()->server('SERVER_PORT'),
            'statamic_version' => Statamic::version(),
            'statamic_pro' => Statamic::pro(),
            'php_version' => PHP_VERSION,
            'packages' => $this->packagePayload(),
        ];
    }

    private function packagePayload()
    {
        return Facades\Addon::all()->mapWithKeys(function ($addon) {
            return [$addon->package() => [
                'version' => $addon->version(),
                'edition' => $addon->edition(),
            ]];
        })->all();
    }

    private function cacheResponse(CarbonInterface $expiration, $contents)
    {
        $contents = array_merge($contents, [
            'expiry' => $expiration->timestamp,
            'payload' => $this->payload(),
        ]);

        $this->cache()->put(self::CACHE_KEY, $contents, $expiration);

        return $contents;
    }

    private function hasCachedResponse()
    {
        if (! $cached = $this->getCachedResponse()) {
            return false;
        }

        return ! $this->payloadHasChanged($cached['payload'], $this->payload());
    }

    private function payloadHasChanged($previous, $current)
    {
        $exclude = ['ip'];

        return Arr::except($previous, $exclude) !== Arr::except($current, $exclude);
    }

    private function getCachedResponse()
    {
        return $this->cache()->get(self::CACHE_KEY);
    }

    public function clearCachedResponse()
    {
        return $this->cache()->forget(self::CACHE_KEY);
    }

    private function handleRequestException(RequestException $e)
    {
        $code = $e->getCode();

        if ($code == 422) {
            return $this->cacheAndReturnValidationResponse($e);
        } elseif ($code == 429) {
            return $this->cacheAndReturnRateLimitResponse($e);
        }

        return $this->cacheAndReturnErrorResponse($e);
    }

    private function cacheAndReturnValidationResponse($e)
    {
        $json = json_decode($e->getResponse()->getBody()->getContents(), true);

        return $this->cacheResponse(now()->addHour(), [
            'error' => 422,
            'errors' => $json['errors'],
        ]);
    }

    private function cacheAndReturnRateLimitResponse($e)
    {
        $seconds = $e->getResponse()->getHeader('Retry-After')[0];

        return $this->cacheResponse(now()->addSeconds($seconds), ['error' => 429]);
    }

    private function cacheAndReturnErrorResponse($e)
    {
        Log::debug('Error contacting Outpost: '.$e->getMessage());

        return $this->cacheResponse(now()->addMinutes(5), ['error' => $e->getCode()]);
    }

    private function cache()
    {
        if ($this->store) {
            return $this->store;
        }

        try {
            $store = Cache::store('outpost');
        } catch (InvalidArgumentException $e) {
            $store = Cache::store();
        }

        return $this->store = $store;
    }
}
