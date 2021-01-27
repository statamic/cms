<?php

namespace Statamic\API\Cachers;

use Closure;
use Illuminate\Http\Request;
use Statamic\API\Cacher;
use Statamic\Events\Event;

class NullCacher implements Cacher
{
    public function remember(Request $request, Closure $callback)
    {
        return $callback();
    }

    public function handleInvalidationEvent(Event $event)
    {
        //
    }
}
