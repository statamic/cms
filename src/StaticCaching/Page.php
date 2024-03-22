<?php

namespace Statamic\StaticCaching;

use Illuminate\Contracts\Support\Responsable;

class Page implements Responsable
{
    public function __construct(public $response, public $headers)
    {
    }

    public function toResponse($request)
    {
        return response($this->response, 200, $this->headers);
    }
}
