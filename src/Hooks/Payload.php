<?php

namespace Statamic\Hooks;

class Payload
{
    public function __construct(private array $payload)
    {
        //
    }

    public function __get($key)
    {
        return $this->payload[$key] ?? null;
    }

    public function __set($key, $value)
    {
        $this->payload[$key] = $value;
    }
}
