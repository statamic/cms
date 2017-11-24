<?php

namespace Statamic\API;

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
}
