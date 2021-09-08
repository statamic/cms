<?php

namespace Statamic\Events;

use Illuminate\Http\Response;

class ResponseCreated extends Event
{
    /**
     * @var Response
     */
    public $response;

    /**
     * @param  Response  $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }
}
