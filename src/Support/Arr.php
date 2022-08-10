<?php

namespace Statamic\Support;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr as IlluminateArr;
use Statamic\Fields\Values;

class Arr extends IlluminateArr
{
    /**
     * Get an item from an array using "dot" or "colon" notation.
     *
     * @param  array  $array
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if ($key) {
            $key = str_replace(':', '.', $key);
        }

        return parent::get($array, $key, $default);
    }

    /**
     * Check if an item from an array exists using "dot" or "colon" notation.
     *
     * @param  array  $array
     * @param  string  $key
     * @return bool
     */
    public static function has($array, $key)
    {
        if ($key) {
            $key = str_replace(':', '.', $key);
        }

        return parent::has($array, $key);
    }

    public static function addScope($array, $scope)
    {
        if (static::isAssoc($array)) {
            $array[$scope] = $array;

            return $array;
        }

        return collect($array)->map(function ($value) use ($scope) {
            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            }

            if (! is_array($value)) {
                throw new Exception('Scopes can only be added to associative or multidimensional arrays.');
            }

            $value[$scope] = $value;

            return $value;
        })->all();
    }

    /**
     * Checks if an array is associative.
     *
     * @param $array
     * @return bool
     */
    public static function assoc($array)
    {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * Deep merges arrays better than array_merge_recursive().
     *
     * @param  array  $array1
     * @param  array  $array2
     * @return array
     */
    public static function &combineRecursive(array &$array1, &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::combineRecursive($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        // Ensure the array will be ordered using the keys in the second array.
        $merged = self::sortByArray($merged, array_keys($array2));

        return $merged;
    }

    /**
     * Recusive friendly method of sanitizing an array.
     *
     * @param  array  $array  The array to sanitize
     * @param  bool  $antlers  Whether Antlers (curly braces) should be escaped.
     * @return array
     */
    public static function sanitize($array, $antlers = true)
    {
        return collect($array)->mapWithKeys(function ($value, $key) use ($antlers) {
            return [Html::sanitize($key) => Html::sanitize($value, $antlers)];
        })->all();
    }

    /**
     * Explodes options into an array.
     *
     * @param  string  $string  String to explode
     * @param  bool  $keyed  Are options keyed?
     * @return array
     */
    public static function explodeOptions($string, $keyed = false)
    {
        $options = explode('|', $string);

        if ($keyed) {
            $temp_options = [];
            foreach ($options as $value) {
                if (strpos($value, ':')) {
                    // key:value pair present
                    [$option_key, $option_value] = explode(':', $value);
                } else {
                    // default value is false
                    $option_key = $value;
                    $option_value = false;
                }

                // set the main options array
                $temp_options[$option_key] = $option_value;
            }
            // reassign and override
            $options = $temp_options;
        }

        return $options;
    }

    /**
     * Normalize arguments.
     *
     * Ensures both ['one', 'two'] or 'one|two' ends up as the former
     *
     * @param mixed
     * @return array
     */
    public static function normalizeArguments($args)
    {
        $output = [];

        foreach ($args as $arg) {
            if (! is_array($arg)) {
                $arg = explode('|', $arg);
            }

            $output = array_merge($output, $arg);
        }

        return array_unique($output);
    }

    /**
     * Picks the first value that isn't null.
     *
     * @return mixed
     */
    public static function pick()
    {
        $args = func_get_args();

        if (! is_array($args) || ! count($args)) {
            return null;
        }

        foreach ($args as $arg) {
            if (! is_null($arg)) {
                return $arg;
            }
        }

        return null;
    }

    /**
     * Sort an array by an array of keys.
     *
     * @param  array  $array  The array to be sorted
     * @param  array  $order  An array of keys in the order to sort the first array
     * @return array
     */
    public static function sortByArray(array $array, array $order)
    {
        $ordered = [];

        foreach ($order as $key) {
            if (array_key_exists($key, $array)) {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
            }
        }

        return $ordered + $array;
    }

    /**
     * Get rid of null values. (Empty arrays, literal null values, and empty strings).
     *
     * @param  array  $array
     * @return array
     */
    public static function removeNullValues($data)
    {
        return array_filter($data, function ($item) {
            return is_array($item)
                ? ! empty($item)
                : ! in_array($item, [null, ''], true);
        });
    }

    /**
     * Get the first item from an array using a list of keys.
     *
     * @param  \ArrayAccess|array  $array
     * @param  array  $keys
     * @param  mixed  $default
     * @return mixed
     */
    public static function getFirst($array, $keys, $default = null)
    {
        $value = collect($keys)
            ->map(function ($key) use ($array) {
                return static::get($array, $key);
            })
            ->reject(function ($value) {
                return $value === null;
            })
            ->first();

        return $value ?? $default;
    }

    public static function undot($dotted)
    {
        $array = [];

        foreach ($dotted as $key => $value) {
            static::set($array, $key, $value);
        }

        return $array;
    }
}
