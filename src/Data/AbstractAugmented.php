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
    protected $isSelecting = false;

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

    public function select($keys = null, $fields = null)
    {
        $arr = [];

        if (! $fields) {
            $fields = $this->blueprintFields();
        }

        $keys = $this->filterKeys(Arr::wrap($keys ?: $this->keys()));

        $this->isSelecting = true;

        foreach ($keys as $key) {
            $arr[$key] = (new TransientValue(null, $key, null))->withAugmentationReferences($this, $fields->get($key));
        }

        $this->isSelecting = false;

        return (new AugmentedCollection($arr))->withRelations($this->relations);
    }

    abstract public function keys();

    public function getAugmentedMethodValue($method)
    {
        if ($this->methodExistsOnThisClass($method)) {
            return $this->$method();
        }

        return $this->data->$method();
    }

    protected function adjustFieldtype($handle, $fieldtype)
    {
        if ($this->isSelecting || $fieldtype !== null) {
            return $fieldtype;
        }

        return $this->getFieldtype($handle);
    }

    public function get($handle, $fieldtype = null): Value
    {
        $method = Str::camel($handle);

        if ($this->methodExistsOnThisClass($method)) {
            $value = $this->wrapAugmentedMethodInvokable($method, $handle, $fieldtype);
        } elseif (method_exists($this->data, $method) && collect($this->keys())->contains(Str::snake($handle))) {
            $value = $this->wrapDataMethodInvokable($method, $handle, $fieldtype);
        } else {
            $value = $this->wrapDeferredValue($handle, $fieldtype);
        }

        // If someone is calling ->get() directly they probably
        // don't want to remember to also ->materialize() it.
        if (! $this->isSelecting) {
            return $value->materialize();
        }

        return $value;
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

    public function getFromData($handle)
    {
        $value = method_exists($this->data, 'value') ? $this->data->value($handle) : $this->data->get($handle);

        if (method_exists($this->data, 'getSupplement')) {
            $value = $this->data->hasSupplement($handle)
                ? $this->data->getSupplement($handle)
                : $value;
        }

        return $value;
    }

    protected function wrapDeferredValue($handle, $fieldtype = null)
    {
        $fieldtype = $this->adjustFieldtype($handle, $fieldtype);

        return (new DeferredValue(
            null,
            $handle,
            $fieldtype,
            $this->data
        ))->withAugmentedReference($this);
    }

    protected function wrapAugmentedMethodInvokable(string $method, string $handle, $fieldtype = null)
    {
        $fieldtype = $this->adjustFieldtype($handle, $fieldtype);

        return (new InvokableValue(
            null,
            $handle,
            $fieldtype,
            $this->data
        ))->setInvokableDetails($method, $this);
    }

    protected function wrapDataMethodInvokable(string $method, string $handle, $fieldtype = null)
    {
        $fieldtype = $this->adjustFieldtype($handle, $fieldtype);

        return (new InvokableValue(
            null,
            $handle,
            $fieldtype,
            $this->data
        ))->setInvokableDetails($method, $this->data);
    }

    protected function wrapValue($value, $handle, $fieldtype = null)
    {
        $fieldtype = $this->adjustFieldtype($handle, $fieldtype);

        return new Value(
            $value,
            $handle,
            $fieldtype,
            $this->data
        );
    }

    protected function getFieldtype($handle)
    {
        return optional($this->blueprintFields()->get($handle))->fieldtype();
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

    public function withRelations($relations)
    {
        $this->relations = $relations;

        return $this;
    }
}
