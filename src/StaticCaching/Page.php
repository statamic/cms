<?php

namespace Statamic\StaticCaching;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;

class Page implements Responsable
{
    public function __construct(public $response, public $headers)
    {
    }

    public function toResponse($request = null)
    {
        return new Response($this->response, 200, $this->headers);
    }
}
