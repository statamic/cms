<?php

namespace Statamic\StaticCaching;

use Illuminate\Http\Response;

class ResponseStatusTracker
{
    private array $responses = [];

    public function set(Response $response, ResponseStatus $status)
    {
        $this->responses[spl_object_id($response)] = $status;
    }

    public function get(Response $response): ?ResponseStatus
    {
        return $this->responses[spl_object_id($response)] ?? null;
    }

    public function registerMacros(): void
    {
        $tracker = $this;

        Response::macro('setStaticCacheResponseStatus', function ($status) use ($tracker) {
            $tracker->set($this, $status);
        });

        Response::macro('wasStaticallyCached', function () use ($tracker) {
            return $tracker->get($this) === ResponseStatus::HIT;
        });
    }
}
