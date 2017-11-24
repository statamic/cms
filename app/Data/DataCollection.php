<?php

namespace Statamic\Data;

use Carbon\Carbon;
use Statamic\API\Str;
use Statamic\API\Helper;
use Statamic\Exceptions\MethodNotFoundException;
use Illuminate\Support\Collection as IlluminateCollection;

/**
 * An abstract collection of data types
 */
abstract class DataCollection extends IlluminateCollection
{
    /**
     * Limit a collection
     *
     * @param $limit
     * @return static
     */
    public function limit($limit)
    {
        return $this->take($limit);
    }

    /**
     * Sort a collection by multiple fields
     *
     * Accepts a string like "title:desc|foo:asc"
     * The keys are optional. "title:desc|foo" is fine.
     *
     * @param string $sort
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

                list($one, $two) = $this->getSortableValues($sort_by, $a, $b);

                $result = Helper::compareValues($one, $two);

                if ($result !== 0) {
                    return ($sort_dir === 'desc') ? $result * -1 : $result;
                }
            }

            return 0;
        });

        return new static($arr);
    }

    /**
     * Get the values from two content objects to be sorted against each other
     *
     * @param string                        $sort The field to be searched
     * @param \Statamic\Contracts\Data\Data $a    The first data object
     * @param \Statamic\Contracts\Data\Data $b    The second data object
     * @return array
     */
    protected function getSortableValues($sort, $a, $b)
    {
        $method = Str::camel($sort);

        $one = (method_exists($a, $method)) ? call_user_func([$a, $method]) : $a->getWithDefaultLocale($sort);
        $two = (method_exists($b, $method)) ? call_user_func([$b, $method]) : $b->getWithDefaultLocale($sort);

        return [$this->normalizeSortableValue($one), $this->normalizeSortableValue($two)];
    }

    /**
     * Make sure the sortable value is in a format suitable for sorting
     *
     * @param mixed $value
     * @return mixed
     */
    protected function normalizeSortableValue($value)
    {
        if ($value instanceof Carbon) {
            $value = $value->timestamp;
        }

        return $value;
    }

    /**
     * Walk over an array of methods and attempt to run each one
     *
     * @param array $actions
     * @return \Statamic\Data\DataCollection
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
     * Filter the Collection by condition(s)
     *
     * @param string $conditions
     * @return \Statamic\Data\DataCollection
     */
    public function conditions($conditions)
    {
        $filterer = app('Statamic\Data\Filters\ConditionFilterer');

        return $filterer->filter($this, $conditions);
    }

    /**
     * Add a new key to each item of the collection
     *
     * @param string|callable $key       New key to add, or a function to return an array of new values
     * @param callable        $callable  Function to return the new value when specifying a key
     * @return \Statamic\Data\DataCollection
     */
    public function supplement($key, callable $callable = null)
    {
        // If a callable is specified as the first parameter, we'll expect that it'll
        // return an associative array of values to be merged into the supplements.
        if (is_callable($key)) {
            return $this->supplementMany($key);
        }

        if (! is_callable($callable, false)) {
            return $this;
        }

        foreach ($this->items as $i => $item) {
            $this->items[$i]->setSupplement($key, call_user_func($callable, $item));
        }

        return $this;
    }

    /**
     * Add a new set of keys to each item of the collection
     *
     * @param callable $callable  Function to return an array of new values
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
     * Get the collection as a plain array
     *
     * @return array
     */
    public function toArray()
    {
        return array_values(parent::toArray());
    }

    /**
     * Get the collection as a plain array using only selected keys
     *
     * @param array $keys
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
                    if (!$data = $item->get($key)) {
                        // Finally try getting a property via its getter method
                        $method = 'get' . ucfirst($key);
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
}
