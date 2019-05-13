<?php

namespace Statamic\Data;

trait SupplementsData
{
    protected $supplements = [];

    public function supplements()
    {
        return $this->supplements;
    }

    public function setSupplement($key, $value)
    {
        $this->supplements[$key] = $value;

        return $this;
    }

    public function getSupplement($key)
    {
        return $this->supplements[$key];
    }
}
