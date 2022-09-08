<?php

namespace Statamic\Data;

use Statamic\Contracts\Data\Augmented;
use Statamic\Fields\Value;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\Str;

abstract class AbstractAugmented implements Augmented
{
    protected $data;
    protected $blueprintFields;
    protected $relations = [];

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

        $keys = $this->filterKeys(Arr::wrap($keys ?: $this->keys()));

        foreach ($keys as $key) {
            $arr[$key] = $this->get($key);
        }

        return (new AugmentedCollection($arr))->withRelations($this->relations);
    }

    abstract public function keys();

    public function get($handle): Value
    {
        $method = Str::camel($handle);

        if ($this->methodExistsOnThisClass($method)) {
            $value = $this->$method();

            return $value instanceof Value
                ? $value
                : new Value($value, $method, null, $this->data);
        }

        if (method_exists($this->data, $method) && collect($this->keys())->contains(Str::snake($handle))) {
            return $this->wrapValue($this->data->$method(), $handle);
        }

        return $this->wrapValue($this->getFromData($handle), $handle);
    }

    protected function filterKeys($keys)
    {
        return array_diff($keys, $this->excludedKeys());
    }

    protected function excludedKeys()
    {
        return Statamic::isApiRoute()
            ? config('statamic.api.excluded_keys', [])
            : [];
    }

    private function methodExistsOnThisClass($method)
    {
        return method_exists($this, $method) && ! in_array($method, ['select', 'except']);
    }

    protected function getFromData($handle)
    {
        $value = method_exists($this->data, 'value') ? $this->data->value($handle) : $this->data->get($handle);

        if (method_exists($this->data, 'getSupplement')) {
            $value = $this->data->hasSupplement($handle)
                ? $this->data->getSupplement($handle)
                : $value;
        }

        return $value;
    }

    protected function wrapValue($value, $handle)
    {
        $fields = $this->blueprintFields();

        return new Value(
            $value,
            $handle,
            optional($fields->get($handle))->fieldtype(),
            $this->data
        );
    }

    protected function blueprintFields()
    {
        if (! isset($this->blueprintFields)) {
            $this->blueprintFields = (method_exists($this->data, 'blueprint') && $blueprint = $this->data->blueprint())
                ? $blueprint->fields()->all()
                : collect();
        }

        return $this->blueprintFields;
    }

    public function withRelations($relations)
    {
        $this->relations = $relations;

        return $this;
    }
}
