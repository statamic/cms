<?php

namespace Statamic\Extend;

trait HasParameters
{
    public function get($keys, $default = null)
    {
        return $this->parameters->get($keys, $default);
    }

    public function getBool($keys, $default = false)
    {
        return $this->parameters->bool($keys, $default);
    }

    public function getFloat($keys, $default = null)
    {
        return $this->parameters->float($keys, $default);
    }

    public function getInt($keys, $default = null)
    {
        return $this->parameters->float($keys, $default);
    }

    public function getParam($keys, $default = null)
    {
        return $this->get($keys, $default);
    }

    public function getList($keys, $default = null)
    {
        return $this->parameters->explode($keys, $default);
    }
}
