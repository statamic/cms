<?php

namespace Statamic\API\Cachers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Statamic\API\Cacher;
use Statamic\Events\Event;

class NullCacher implements Cacher
{
    public function get(Request $request)
    {
        //
    }

    public function put(Request $request, JsonResponse $response)
    {
        //
    }

    public function handleInvalidationEvent(Event $event)
    {
        //
    }
}
