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
    private $fieldtype;

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
            $arr[$key] = $this->transientValue($key);
        }

        return (new AugmentedCollection($arr))->withRelations($this->relations);
    }

    private function transientValue($key)
    {
        $fields = $this->blueprintFields();

        $callback = function (Value $value) use ($key, $fields) {
            $this->fieldtype = $fields->get($key)?->fieldtype();
            $deferred = $this->get($key);
            $this->fieldtype = null;

            $value->setFieldtype($deferred->fieldtype());
            $value->setAugmentable($deferred->augmentable());

            return $deferred->raw();
        };

        return new Value($callback, $key);
    }

    abstract public function keys();

    public function get($handle): Value
    {
        $method = Str::camel($handle);

        if ($this->methodExistsOnThisClass($method)) {
            $value = $this->wrapAugmentedMethodInvokable($method, $handle);
        } elseif ($this->methodExistsOnData($handle, $method)) {
            $value = $this->wrapDataMethodInvokable($method, $handle);
        } else {
            $value = $this->wrapDeferredValue($handle);
        }

        return $value->resolve();
    }

    private function filterKeys($keys)
    {
        return array_diff($keys, $this->excludedKeys());
    }

    private function excludedKeys()
    {
        return Statamic::isApiRoute()
            ? config('statamic.api.excluded_keys', [])
            : [];
    }

    private function methodExistsOnThisClass(string $method): bool
    {
        return method_exists($this, $method) && ! in_array($method, ['select', 'except']);
    }

    private function methodExistsOnData(string $handle, string $method): bool
    {
        return method_exists($this->data, $method)
            && collect($this->keys())->contains(Str::snake($handle))
            && ! in_array($handle, ['hook', 'value', 'entry']);
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

    protected function wrapDeferredValue($handle)
    {
        return new Value(
            fn () => $this->getFromData($handle),
            $handle,
            $this->fieldtype($handle),
            $this->data
        );
    }

    protected function wrapAugmentedMethodInvokable(string $method, string $handle)
    {
        return new Value(
            fn () => $this->$method(),
            $handle,
            null,
            $this->data,
        );
    }

    protected function wrapDataMethodInvokable(string $method, string $handle)
    {
        return new Value(
            fn () => $this->data->$method(),
            $handle,
            $this->fieldtype($handle),
            $this->data
        );
    }

    protected function wrapValue($value, $handle)
    {
        return new Value(
            $value,
            $handle,
            $this->fieldtype($handle),
            $this->data
        );
    }

    private function fieldtype($handle)
    {
        return $this->fieldtype ?? optional($this->blueprintFields()->get($handle))->fieldtype();
    }

    public function blueprintFields()
    {
        if (! isset($this->blueprintFields)) {
            $this->blueprintFields = (method_exists($this->data, 'blueprint') && $blueprint = $this->data->blueprint())
                ? $blueprint->fields()->all()
                : collect();
        }

        return $this->blueprintFields;
    }

    public function withBlueprintFields($fields)
    {
        $this->blueprintFields = $fields;

        return $this;
    }

    public function withRelations($relations)
    {
        $this->relations = $relations;

        return $this;
    }
}
