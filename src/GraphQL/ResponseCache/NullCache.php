<?php

namespace Statamic\GraphQL\ResponseCache;

use Illuminate\Http\Request;
use Statamic\Contracts\GraphQL\ResponseCache;
use Statamic\Events\Event;

class NullCache implements ResponseCache
{
    public function get(Request $request)
    {
        //
    }

    public function put(Request $request, $response)
    {
        //
    }

    public function handleInvalidationEvent(Event $event)
    {
        //
    }
}
