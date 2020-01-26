<?php

namespace Statamic\Data;

use Statamic\Fields\Value;
use Statamic\Support\Arr;
use Statamic\Support\Str;

abstract class Augmented
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function all()
    {
        return $this->select();
    }

    public function except($keys)
    {
        return $this->select(array_diff($this->keys(), Arr::wrap($keys)));
    }

    public function select($keys = null)
    {
        $arr = [];

        $keys = Arr::wrap($keys ?: $this->keys());

        foreach ($keys as $key) {
            $arr[$key] = $this->get($key);
        }

        return $arr;
    }

    abstract protected function keys();

    public function get($handle)
    {
        if (method_exists($this, $method = Str::camel($handle))) {
            return $this->$method();
        }

        if (method_exists($this->data, $method = Str::camel($handle))) {
            return $this->data->$method();
        }

        if (! $value = $this->data->get($handle)) {
            return null;
        }

        $fields = (method_exists($this->data, 'blueprint') && $blueprint = $this->data->blueprint())
            ? $blueprint->fields()->all()
            : collect();

        if (! $fields->has($handle)) {
            return $value;
        }

        return new Value(
            $value,
            $handle,
            $fields->get($handle)->fieldtype(),
            $this->data
        );
    }
}
