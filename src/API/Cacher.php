<?php

namespace Statamic\API;

use Closure;
use Illuminate\Http\Request;
use Statamic\Events\Event;

interface Cacher
{
    /**
     * Remember an API request.
     *
     * @param \Illuminate\Http\Request $request     Request associated with the endpoint to be cached
     * @param Closure                  $callback    The reponse callback to be cached
     */
    public function remember(Request $request, Closure $callback);

    /**
     * Handle event based API cache invalidation.
     *
     * @param Event $event
     * @return void
     */
    public function handleInvalidationEvent(Event $event);
}
