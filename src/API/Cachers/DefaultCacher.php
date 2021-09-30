<?php

namespace Statamic\API\Cachers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Statamic\API\AbstractCacher;
use Statamic\Events\Event;

class DefaultCacher extends AbstractCacher
{
    /**
     * {@inheritdoc}
     */
    public function get(Request $request)
    {
        return Cache::get($this->getCacheKey($request));
    }

    /**
     * {@inheritdoc}
     */
    public function put(Request $request, JsonResponse $response)
    {
        $key = $this->trackEndpoint($request);

        Cache::put($key, $response, $this->cacheExpiry());
    }

    /**
     * Handle event based API cache invalidation.
     *
     * @param  Event  $event
     * @return void
     */
    public function handleInvalidationEvent(Event $event)
    {
        $this->getTrackedResponses()->each(function ($key) {
            Cache::forget($key);
        });

        Cache::forget($this->getTrackingKey());
    }

    /**
     * Track endpoint and return new cache key.
     *
     * @param  Request  $request
     * @return string
     */
    protected function trackEndpoint($request)
    {
        $newKey = $this->getCacheKey($request);

        $keys = $this
            ->getTrackedResponses()
            ->push($newKey)
            ->unique()
            ->values()
            ->all();

        Cache::put($this->getTrackingKey(), $keys);

        return $newKey;
    }

    /**
     * Get tracked responses.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getTrackedResponses()
    {
        return collect(Cache::get($this->getTrackingKey(), []));
    }

    /**
     * Get tracking cache key for storing invalidatable endpoints.
     *
     * @return string
     */
    protected function getTrackingKey()
    {
        return $this->normalizeKey('tracked-responses');
    }

    /**
     * Get cache key for endpoint.
     *
     * @param  Request  $request
     * @return string
     */
    protected function getCacheKey(Request $request)
    {
        $domain = $request->root();
        $fullUrl = $request->fullUrl();

        $key = str_replace($domain, '', $fullUrl);

        return $this->normalizeKey($key);
    }
}
