<?php

namespace Statamic\Data;

trait ContainsSupplementalData
{
    protected $supplements;

    public function supplements()
    {
        return $this->supplements;
    }

    public function setSupplement($key, $value)
    {
        $this->supplements[$key] = $value;

        return $this;
    }

    public function getSupplement($key, $fallback = null)
    {
        return $this->hasSupplement($key) ? $this->supplements[$key] : $fallback;
    }

    public function hasSupplement($key)
    {
        return collect($this->supplements)->has($key);
    }
}
