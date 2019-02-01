<?php

namespace Statamic\Data;

trait ContainsData
{
    protected $data = [];
    protected $supplements = [];

    public function get($key, $fallback = null)
    {
        return $this->data[$key] ?? $fallback;
    }

    public function has($key)
    {
        return $this->get($key) != null;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function remove($key)
    {
        unset($this->data[$key]);

        return $this;
    }

    public function data($data = null)
    {
        if (is_null($data)) {
            return $this->data;
        }

        $this->data = $data;

        return $this;
    }

    public function merge($data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

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
