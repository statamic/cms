<?php

namespace Statamic\StaticCaching;

use Illuminate\Http\Response;

class ResponseStatusTracker
{
    private array $responses = [];

    public function set(Response $response, ResponseStatus $status): void
    {
        $this->responses[spl_object_id($response)] = $status;
    }

    public function get(Response $response): ResponseStatus
    {
        return $this->responses[spl_object_id($response)] ?? ResponseStatus::UNDEFINED;
    }

    public function registerMacros(): void
    {
        $tracker = $this;

        Response::macro('setStaticCacheResponseStatus', fn ($status) => $tracker->set($this, $status));

        Response::macro('staticCacheResponseStatus', fn () => $tracker->get($this));

        Response::macro('wasStaticallyCached', fn () => $this->staticCacheResponseStatus() === ResponseStatus::HIT);
    }
}
