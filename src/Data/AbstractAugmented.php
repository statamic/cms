<?php

namespace Statamic\Data;

use Statamic\Contracts\Data\Augmented;
use Statamic\Fields\Value;
use Statamic\Support\Arr;
use Statamic\Support\Str;

abstract class AbstractAugmented implements Augmented
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

        return new AugmentedCollection($arr);
    }

    abstract public function keys();

    public function get($handle)
    {
        $method = Str::camel($handle);

        if ($this->methodExistsOnThisClass($method)) {
            return $this->$method();
        }

        if (method_exists($this->data, $method) && collect($this->keys())->contains(Str::snake($handle))) {
            return $this->wrapValue($this->data->$method(), $handle);
        }

        return $this->wrapValue($this->getFromData($handle), $handle);
    }

    private function methodExistsOnThisClass($method)
    {
        return method_exists($this, $method) && ! in_array($method, ['select', 'except']);
    }

    protected function getFromData($handle)
    {
        $value = method_exists($this->data, 'value') ? $this->data->value($handle) : $this->data->get($handle);

        if (method_exists($this->data, 'getSupplement')) {
            $value = $this->data->getSupplement($handle) ?? $value;
        }

        return $value;
    }

    protected function wrapValue($value, $handle)
    {
        $fields = $this->blueprintFields();

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

    protected function blueprintFields()
    {
        return (method_exists($this->data, 'blueprint') && $blueprint = $this->data->blueprint())
            ? $blueprint->fields()->all()
            : collect();
    }
}
