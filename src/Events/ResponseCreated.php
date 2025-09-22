<?php

namespace Statamic\Events;

use Illuminate\Http\Response;

class ResponseCreated extends Event
{
    public function __construct(public Response $response, public $data)
    {
    }
}
