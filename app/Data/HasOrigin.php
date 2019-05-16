<?php

namespace Statamic\Data;

trait HasOrigin
{
    protected $origin;

    public function values()
    {
        $originValues = $this->hasOrigin() ? $this->origin()->values() : [];

        return array_merge($originValues, $this->data);
    }

    public function value($key)
    {
        return $this->get($key)
            ?? ($this->hasOrigin() ? $this->origin()->value($key) : null);
    }

    public function origin($origin = null)
    {
        return $this->fluentlyGetOrSet('origin')->args(func_get_args());
    }

    public function hasOrigin()
    {
        return $this->origin !== null;
    }
}
