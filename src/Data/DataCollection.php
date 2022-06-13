<?php

namespace Statamic\Data;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Exceptions\MethodNotFoundException;
use Statamic\Facades\Compare;
use Statamic\Support\Str;

/**
 * An abstract collection of data types.
 */
class DataCollection extends IlluminateCollection
{
    /**
     * Limit a collection.
     *
     * @param $limit
     * @return static
     */
    public function limit($limit)
    {
        return $this->take($limit);
    }

    /**
     * Sort a collection by multiple fields.
     *
     * Accepts a string like "title:desc|foo:asc"
     * The keys are optional. "title:desc|foo" is fine.
     *
     * @param  string  $sort
     * @return static
     */
    public function multisort($sort)
    {
        // Short circuit here with support for a random sort order.
        if ($sort === 'random') {
            return new static($this->shuffle()->all());
        }

        $sorts = explode('|', $sort);

        $arr = $this->all();

        uasort($arr, function ($a, $b) use ($sorts) {
            foreach ($sorts as $sort) {
                $bits = explode(':', $sort);
                $sort_by = $bits[0];
                $sort_dir = array_get($bits, 1);

                [$one, $two] = $this->getSortableValues($sort_by, $a, $b);

                $result = Compare::values($one, $two);

                if ($result !== 0) {
                    return ($sort_dir === 'desc') ? $result * -1 : $result;
                }
            }

            return 0;
        });

        return new static($arr);
    }

    /**
     * Get the values from two content objects to be sorted against each other.
     *
     * @param  string  $sort  The field to be searched
     * @param  \Statamic\Contracts\Data\Data  $a  The first data object
     * @param  \Statamic\Contracts\Data\Data  $b  The second data object
     * @return array
     */
    protected function getSortableValues($sort, $a, $b)
    {
        return [
            $this->getSortableValue($sort, $a),
            $this->getSortableValue($sort, $b),
        ];
    }

    protected function getSortableValue($sort, $item)
    {
        if (is_array($item)) {
            return $this->normalizeSortableValue($item[$sort] ?? null);
        }

        $method = Str::camel($sort);

        $value = (method_exists($item, $method))
            ? call_user_func([$item, $method])
            : $item->get($sort);

        return $this->normalizeSortableValue($value);
    }

    /**
     * Make sure the sortable value is in a format suitable for sorting.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function normalizeSortableValue($value)
    {
        if (is_array($value)) {
            $value = count($value)
                ? $this->normalizeSortableValue(array_values($value)[0])
                : null;
        } elseif ($value instanceof Carbon) {
            $value = $value->timestamp;
        }

        return $value;
    }

    /**
     * Walk over an array of methods and attempt to run each one.
     *
     * @param  array  $actions
     * @return \Statamic\Data\DataCollection
     *
     * @throws \Statamic\Exceptions\MethodNotFoundException
     */
    public function actions($actions)
    {
        $collection = $this;

        foreach ($actions as $method => $arguments) {
            if (! method_exists($this, $method)) {
                throw new MethodNotFoundException("The `$method` method doesn't exist.");
            }

            $collection = call_user_func_array([$collection, $method], (array) $arguments);
        }

        return $collection;
    }

    /**
     * Add a new key to each item of the collection.
     *
     * @param  string|callable  $key  New key to add, or a function to return an array of new values
     * @param  mixed  $callable  Function to return the new value when specifying a key
     * @return \Statamic\Data\DataCollection
     */
    public function supplement($key, $callable = null)
    {
        // If a callable is specified as the first parameter, we'll expect that it'll
        // return an associative array of values to be merged into the supplements.
        if ($key instanceof Closure) {
            return $this->supplementMany($key);
        }

        foreach ($this->items as $i => $item) {
            $value = $callable instanceof Closure ? $callable($item) : $callable;
            $this->items[$i]->setSupplement($key, $value);
        }

        return $this;
    }

    /**
     * Add a new set of keys to each item of the collection.
     *
     * @param  callable  $callable  Function to return an array of new values
     * @return static
     */
    public function supplementMany(callable $callable)
    {
        foreach ($this->items as $i => $item) {
            foreach (call_user_func($callable, $item) as $key => $value) {
                $this->items[$i]->setSupplement($key, $value);
            }
        }

        return $this;
    }

    /**
     * Get the collection as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_values(parent::toArray());
    }

    /**
     * Get the collection as a plain array using only selected keys.
     *
     * @param  array  $keys
     * @return array
     */
    public function toArrayWith($keys)
    {
        $array = [];

        foreach ($this->items as $i => $item) {
            foreach ($keys as $key) {
                // First try to get the supplemented value
                if (! $data = $item->getSupplement($key)) {
                    // Then try the data / front-matter
                    if (! $data = $item->get($key)) {
                        // Finally try getting a property via its getter method
                        $method = 'get'.ucfirst($key);
                        if (method_exists($item, $method)) {
                            $data = call_user_func([$item, $method]);
                        }
                    }
                }

                $array[$i][$key] = $data;
            }
        }

        return array_values($array);
    }

    public function preProcessForIndex()
    {
        return $this->each(function ($item) {
            $blueprint = $item->blueprint();
            foreach ($item->values() as $key => $value) {
                if ($field = $blueprint->field($key)) {
                    $processed = $field->setValue($value)->setParent($item)->preProcessIndex()->value();
                    $item->setSupplement($key, $processed);
                }
            }
        });
    }
}
