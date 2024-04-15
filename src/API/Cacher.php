<?php

namespace Statamic\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Statamic\Events\Event;

interface Cacher
{
    /**
     * Get a response from the cache.
     *
     * @return JsonResponse|null
     */
    public function get(Request $request);

    /**
     * Put a response into the cache.
     *
     * @return void
     */
    public function put(Request $request, JsonResponse $response);

    /**
     * Handle event based API cache invalidation.
     *
     * @return void
     */
    public function handleInvalidationEvent(Event $event);
}
