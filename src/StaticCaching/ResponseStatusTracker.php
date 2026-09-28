<?php

namespace Statamic\StaticCaching;

use Illuminate\Http\Response;
use WeakMap;

class ResponseStatusTracker
{
    private WeakMap $responses;

    public function __construct()
    {
        $this->responses = new WeakMap;
    }

    public function set(Response $response, ResponseStatus $status): void
    {
        $this->responses[$response] = $status;
    }

    public function get(Response $response): ResponseStatus
    {
        return $this->responses[$response] ?? ResponseStatus::UNDEFINED;
    }

    public function registerMacros(): void
    {
        $tracker = $this;

        Response::macro('setStaticCacheResponseStatus', fn ($status) => $tracker->set($this, $status));

        Response::macro('staticCacheResponseStatus', fn () => $tracker->get($this));

        Response::macro('wasStaticallyCached', fn () => $this->staticCacheResponseStatus() === ResponseStatus::HIT);
    }
}
