<?php

namespace Statamic\Support\Traits;

trait InvadesProperties
{
    protected function invade($object, $property)
    {
        if (! $property) {
            return null;
        }

        if (is_string($property) || ! is_callable($property)) {
            return (fn () => $this->{$property})->call($object);
        }

        return $property->call($object);
    }

    protected function invadeSetter($object, $property, $value = null)
    {
        if (! $property) {
            return;
        }

        if (is_string($property) || ! is_callable($property)) {
            (fn () => $this->{$property} = $value)->call($object);

            return;
        }

        $property->call($object);
    }
}
