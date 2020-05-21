<?php

namespace Statamic\Licensing;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades;
use Statamic\Statamic;

class Outpost
{
    const ENDPOINT = 'https://outpost.statamic.test/v3/query';
    const REQUEST_TIMEOUT = 5;
    const CACHE_KEY = 'statamic.outpost.response';

    private $response;
    private $client;

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
        if ($this->hasCachedResponse()) {
            return $this->getCachedResponse();
        }

        try {
            return $this->performAndCacheRequest();
        } catch (ConnectException $e) {
            return $this->cacheAndReturnErrorResponse();
        } catch (RequestException $e) {
            return $this->handleRequestException($e);
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
            'verify' => false,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function payload()
    {
        return [
            'key' => config('statamic.system.license_key'),
            'host' => request()->getHost(),
            'statamic_version' => Statamic::version(),
            'statamic_pro' => Statamic::pro(),
            'packages' => $this->packagePayload(),
        ];
    }

    private function packagePayload()
    {
        return Facades\Addon::all()->mapWithKeys(function ($addon) {
            return [$addon->package() => $addon->version()];
        })->all();
    }

    private function cacheResponse(Carbon $expiration, $contents)
    {
        $contents = array_merge($contents, [
            'expiry' => $expiration->timestamp,
        ]);

        Cache::put(self::CACHE_KEY, $contents, $expiration);

        return $contents;
    }

    private function hasCachedResponse()
    {
        return Cache::has(self::CACHE_KEY);
    }

    private function getCachedResponse()
    {
        return Cache::get(self::CACHE_KEY);
    }

    public function clearCachedResponse()
    {
        return Cache::forget(self::CACHE_KEY);
    }

    private function handleRequestException(RequestException $e)
    {
        switch ($e->getCode()) {
            case 422:
                return $this->cacheAndReturnValidationResponse($e);
            case 429:
                return $this->cacheAndReturnRateLimitResponse($e);
            case 500:
                return $this->cacheAndReturnErrorResponse();
        }

        throw $e;
    }

    private function cacheAndReturnValidationResponse($e)
    {
        $json = json_decode($e->getResponse()->getBody()->getContents(), true);

        return $this->cacheResponse(now()->addHour(), [
            'error' => 422,
            'errors' => $json['errors']
        ]);
    }

    private function cacheAndReturnRateLimitResponse($e)
    {
        $seconds = $e->getResponse()->getHeader('Retry-After')[0];

        return $this->cacheResponse(now()->addSeconds($seconds), ['error' => 429]);
    }

    private function cacheAndReturnErrorResponse()
    {
        return $this->cacheResponse(now()->addMinutes(5), ['error' => 500]);
    }
}
