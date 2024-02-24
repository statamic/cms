<?php

namespace Statamic\Support\Traits;

trait InvadesProperties
{
    protected function invade($object, $property)
    {
        if (! is_callable($property)) {
            return (fn () => $this->{$property})->call($object);
        }

        return $property->call($object);
    }
}
