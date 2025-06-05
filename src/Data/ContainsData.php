<?php

namespace Statamic\Data;

trait ContainsData
{
    use ContainsSupplementalData;

    protected $data;

    public function get($key, $fallback = null)
    {
        return data_get($this->data, $key, $fallback);
    }

    public function has($key)
    {
        return $this->data->has($key);
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function increment($key, $amount = 1)
    {
        $this->data[$key] = ($this->data[$key] ?? 0) + $amount;

        return $this;
    }

    public function decrement($key, $amount = 1)
    {
        return $this->increment($key, -$amount);
    }

    public function remove($key)
    {
        unset($this->data[$key]);

        return $this;
    }

    public function modify($key, $callback)
    {
        $value = $this->get($key);

        $this->set($key, $callback($value));

        return $this;
    }

    public function data($data = null)
    {
        if (func_num_args() === 0) {
            return $this->data;
        }

        $this->data = collect($data);

        return $this;
    }

    public function merge($data)
    {
        $this->data = $this->data->merge($data);

        return $this;
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }
}
