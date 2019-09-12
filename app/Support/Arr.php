<?php

namespace Statamic\Support;

use Statamic\Data\DataCollection;
use Illuminate\Support\Arr as IlluminateArr;

class Arr extends IlluminateArr
{
    /**
     * Checks if an array is associative
     *
     * @param $array
     * @return bool
     */
    public static function assoc($array)
    {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * Deep merges arrays better than array_merge_recursive()
     *
     * @param array $array1
     * @param array $array2
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
     * Explodes options into an array
     *
     * @param string  $string  String to explode
     * @param bool $keyed  Are options keyed?
     * @return array
     */
    public function explodeOptions($string, $keyed = false)
    {
        $options = explode('|', $string);

        if ($keyed) {

            $temp_options = array();
            foreach ($options as $value) {

                if (strpos($value, ':')) {
                    # key:value pair present
                    list($option_key, $option_value) = explode(':', $value);
                } else {
                    # default value is false
                    $option_key = $value;
                    $option_value = false;
                }

                # set the main options array
                $temp_options[$option_key] = $option_value;
            }
            # reassign and override
            $options = $temp_options;
        }

        return $options;
    }

    /**
     * Checks if $value is an empty array
     *
     * @param mixed  $value  Value to check
     * @return bool
     */
    public function isEmpty($value)
    {
        if (is_array($value)) {
            foreach ($value as $subvalue) {
                if (!self::isEmptyArray($subvalue)) {
                    return false;
                }
            }
        } elseif (!empty($value) || $value !== '') {
            return false;
        }

        return true;
    }

    /**
     * Normalize arguments
     *
     * Ensures both ['one', 'two'] or 'one|two' ends up as the former
     *
     * @param mixed
     * @return array
     */
    public function normalizeArguments($args)
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
     * Picks the first value that isn't null
     *
     * @return mixed
     */
    public function pick()
    {
        $args = func_get_args();

        if (!is_array($args) || !count($args)) {
            return null;
        }

        foreach ($args as $arg) {
            if (!is_null($arg)) {
                return $arg;
            }
        }

        return null;
    }

    /**
     * Sort an array by an array of keys
     *
     * @param  array  $array The array to be sorted
     * @param  array  $order An array of keys in the order to sort the first array
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

    public static function addScope($data, $scope)
    {
        if ($data instanceof DataCollection) {
            $data = $data->toArray();
        }

        // If it's already an associative array, we can just grab
        // the whole thing and duplicate it into its own scope.
        if (self::assoc($data)) {
            $data[$scope] = $data;
            return $data;
        }

        $output = [];

        foreach ($data as $i => $iteration) {
            if (is_array($iteration)) {
                foreach ($iteration as $key => $val) {
                    $output[$i][$scope][$key] = $val;
                    $output[$i][$key] = $val;
                }
            } else {
                $output[$scope][$i] = $iteration;
                $output[$i] = $iteration;
            }
        }

        return $output;
    }

    /**
     * Get rid of null values. (Empty arrays, literal null values, and empty strings)
     *
     * @param array $array
     * @return array
     */
    public static function removeNullValues($data)
    {
        return array_filter($data, function ($item) {
            return is_array($item)
                ? !empty($item)
                : !in_array($item, [null, ''], true);
        });
    }

    public static function filterRecursive($input)
    {
        foreach ($input as &$value) {
           if (is_array($value)) {
               $value = self::filterRecursive($value);
           }
        }

        return array_filter($input, function($var) {
            return !is_null($var);
        });
    }

    /**
     * Get the first item from an array using a list of keys.
     *
     * @param \ArrayAccess|array $array
     * @param array $keys
     * @param mixed $default
     * @return mixed
     */
    public static function getFirst($array, $keys, $default = null)
    {
        $value = collect($keys)
            ->map(function ($key) use ($array) {
                return static::get($array, $key);
            })
            ->filter()
            ->first();

        return $value ?? $default;
    }
}
