<?php

namespace Statamic\Data;

trait ContainsCascadingData
{
    protected $cascade;

    public function cascade($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->cascade;
        }

        if (is_array($key)) {
            $this->cascade = collect($key);

            return $this;
        }

        return $this->cascade->get($key, $default);
    }
}
