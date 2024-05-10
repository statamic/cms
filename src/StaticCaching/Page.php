<?php

namespace Statamic\StaticCaching;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;

class Page implements Responsable
{
    public function __construct(public string $content, public array $headers = [])
    {
    }

    public function toResponse($request): Response
    {
        return new Response($this->content, 200, $this->headers);
    }
}
