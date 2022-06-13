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
     * @param  Request  $request
     * @return JsonResponse|null
     */
    public function get(Request $request);

    /**
     * Put a response into the cache.
     *
     * @param  Request  $request
     * @param  JsonResponse  $response
     * @return void
     */
    public function put(Request $request, JsonResponse $response);

    /**
     * Handle event based API cache invalidation.
     *
     * @param  Event  $event
     * @return void
     */
    public function handleInvalidationEvent(Event $event);
}
