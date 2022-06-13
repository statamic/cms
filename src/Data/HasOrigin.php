<?php

namespace Statamic\Data;

trait HasOrigin
{
    protected $origin;

    public function values()
    {
        $originValues = $this->hasOrigin() ? $this->origin()->values() : collect();

        return $originValues->merge($this->data);
    }

    public function value($key)
    {
        return $this->values()->get($key);
    }

    public function origin($origin = null)
    {
        return $this->fluentlyGetOrSet('origin')
            ->getter(function ($origin) {
                if (is_string($origin)) {
                    $this->origin = $origin = $this->getOriginByString($origin);
                }

                return $origin;
            })
            ->args(func_get_args());
    }

    abstract public function getOriginByString($origin);

    public function hasOrigin()
    {
        return $this->origin() !== null;
    }

    public function isRoot()
    {
        return ! $this->hasOrigin();
    }

    public function root()
    {
        $entry = $this;

        while ($entry->hasOrigin()) {
            $entry = $entry->origin();
        }

        return $entry;
    }
}
