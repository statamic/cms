<?php

namespace Statamic\Contracts\GraphQL;

use Illuminate\Http\Request;
use Statamic\Events\Event;

interface ResponseCache
{
    public function get(Request $request);

    public function put(Request $request, $response);

    public function handleInvalidationEvent(Event $event);
}
