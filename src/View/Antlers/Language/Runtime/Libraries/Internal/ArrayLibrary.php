<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries\Internal;

use Statamic\Support\Arr;
use Statamic\View\Antlers\Language\Runtime\Libraries\RuntimeLibrary;

class ArrayLibrary extends RuntimeLibrary
{
    protected $name = ['arr', 'array'];

    protected $exposedMethods = [];

    public function __construct()
    {
        $this->exposedMethods = [
            'explode' => [
                [
                    self::KEY_NAME => 'delimiter',
                    self::KEY_HAS_DEFAULT => false,
                    self::KEY_ACCEPTS => [self::KEY_TYPE_STRING],
                ],
                [
                    self::KEY_NAME => 'string',
                    self::KEY_HAS_DEFAULT => false,
                    self::KEY_ACCEPTS => [self::KEY_TYPE_STRING],
                ],
            ],
            'implode' => [$this->stringVar('delimiter'), $this->arrayVar('array')],
            'get' => [
                $this->arrayVar('array'),
                $this->stringVar('key'),
                $this->anyWithNullDefault('default'),
            ],
            'has' => [
                $this->arrayVar('array'),
                $this->stringVar('key'),
            ],
            'hasAny' => [
                $this->arrayVar('array'),
                [
                    self::KEY_NAME => 'keys',
                    self::KEY_ACCEPTS => [
                        self::KEY_TYPE_ARRAY,
                        self::KEY_TYPE_STRING,
                    ],
                ],
            ],
            'assoc' => [$this->arrayVar('array')],
            'isAssoc' => [$this->arrayVar('array')],
            'dot' => [
                $this->arrayVar('array'),
                $this->stringVarWithDefault('prepend', ''),
            ],
            'add' => [
                $this->arrayVar('array'),
                $this->stringVar('key'),
                $this->anyParam('value'),
            ],
            'collapse' => [$this->arrayVar('array')],
            'crossJoin' => [
                [
                    self::KEY_NAME => 'arrays',
                    self::KEY_ACCEPTS_MANY => true,
                    self::KEY_ACCEPTS => [self::KEY_TYPE_ARRAY],
                ],
            ],
            'divide' => [$this->arrayVar('array')],
            'except' =>  [
                $this->arrayVar('array'),
                [
                    self::KEY_NAME => 'keys',
                    self::KEY_ACCEPTS => [
                        self::KEY_TYPE_STRING,
                        self::KEY_TYPE_ARRAY,
                        self::KEY_TYPE_NUMERIC,
                    ],
                ],
            ],
            'exists' => [
                $this->arrayVar('array'),
                [
                    self::KEY_NAME => 'key',
                    self::KEY_ACCEPTS => [
                        self::KEY_TYPE_STRING,
                        self::KEY_TYPE_NUMERIC,
                    ],
                ],
            ],
            'flatten' => [
                $this->arrayVar('array'),
                $this->numericVarWithDefault('depth', INF),
            ],
            'forget' => [
                $this->arrayVarReference('array'),
                [
                    self::KEY_NAME => 'keys',
                    self::KEY_ACCEPTS => [
                        self::KEY_TYPE_STRING,
                        self::KEY_TYPE_ARRAY,
                        self::KEY_TYPE_NUMERIC,
                    ],
                ],
            ],
            'only' => [
                $this->arrayVar('array'),
                $this->arrayVar('keys'),
            ],
            'pluck' => [
                $this->arrayVar('array'),
                $this->anyParam('value'),
                $this->anyWithNullDefault('key'),
            ],
            'prepend' => [
                $this->arrayVar('array'),
                $this->anyParam('value'),
                $this->anyWithNullDefault('key'),
            ],
            'pull' => [
                $this->arrayVarReference('array'),
                $this->anyParam('key'),
                $this->anyWithNullDefault('default'),
            ],
            'random' => [
                $this->arrayVar('array'),
                $this->numericVarWithDefault('number', null),
                $this->boolVarWithDefault('preserveKeys', false),
            ],
            'set' => [
                $this->arrayVarReference('array'),
                $this->stringVar('key'),
                $this->anyParam('value'),
            ],
            'shuffle' => [
                $this->arrayVar('array'),
                $this->numericVarWithDefault('seed', null),
            ],
            'query' => [$this->arrayVar('array')],
            'wrap' => [$this->anyParam('wrap')],
            'SORT_REGULAR' => 1, 'SORT_NUMERIC' => 1, 'SORT_STRING' => 1, 'SORT_LOCALE_STRING' => 1,
            'SORT_NATURAL' => 1, 'SORT_FLAG_CASE' => 1, 'COUNT_NORMAL' => 1, 'COUNT_RECURSIVE' => 1,

            'isArray' => [$this->anyParam('value')],
            'inArray' => [$this->anyParam('needle'), $this->arrayVar('haystack'), $this->boolVarWithDefault('strict', false)],
            'unique' => [$this->arrayVar('array'), $this->numericVarWithDefault('sort_flags', SORT_STRING)],
            'search' => [$this->anyParam('needle'), $this->arrayVar('haystack'), $this->boolVarWithDefault('strict', false)],
            'reverse' => [$this->arrayVar('array'), $this->boolVarWithDefault('preserve_keys', false)],
            'diff' => [
                [
                    self::KEY_NAME => 'arrays',
                    self::KEY_ACCEPTS_MANY => true,
                    self::KEY_ACCEPTS => [self::KEY_TYPE_ARRAY],
                ],
            ],
            'count' => [$this->arrayVar('array'), $this->numericVarWithDefault('mode', COUNT_NORMAL)],
            'max' => [$this->arrayVar('array')],
            'min' => [$this->arrayVar('array')],
            'countValues' => [$this->arrayVar('array')],
            'sort' => [$this->arrayVarReference('array'), $this->numericVarWithDefault('sort_flags', SORT_REGULAR)],
            'asort' => [$this->arrayVarReference('array'), $this->numericVarWithDefault('sort_flags', SORT_REGULAR)],
            'rsort' => [$this->arrayVarReference('array'), $this->numericVarWithDefault('sort_flags', SORT_REGULAR)],
            'arsort' => [$this->arrayVarReference('array'), $this->numericVarWithDefault('sort_flags', SORT_REGULAR)],
            'keyExists' => [$this->anyParam('key'), $this->arrayVar('array')],
            'keys' => [$this->arrayVar('array')],
            'values' => [$this->arrayVar('values')],
            'push' => [
                $this->arrayVarReference('array'),
                [
                    self::KEY_NAME => 'values',
                    self::KEY_ACCEPTS_MANY => true,
                    self::KEY_ACCEPTS => [
                        self::KEY_TYPE_ARRAY,
                        self::KEY_TYPE_OBJECT,
                        self::KEY_TYPE_NUMERIC,
                        self::KEY_TYPE_STRING,
                        self::KEY_TYPE_BOOL,
                    ],
                ],
            ],
            'slice' => [
                $this->arrayVar('array'),
                $this->numericVar('offset'),
                $this->numericVarWithDefault('length', null),
                $this->boolVarWithDefault('preserve_keys', false),
            ],
            'flip' => [$this->arrayVar('array')],
            'shift' => [$this->arrayVarReference('array')],
            'unshift' => [
                $this->arrayVarReference('array'),
                [
                    self::KEY_NAME => 'values',
                    self::KEY_ACCEPTS_MANY => true,
                    self::KEY_ACCEPTS => [
                        self::KEY_TYPE_ARRAY,
                        self::KEY_TYPE_OBJECT,
                        self::KEY_TYPE_NUMERIC,
                        self::KEY_TYPE_STRING,
                        self::KEY_TYPE_BOOL,
                    ],
                ],
            ],
            'splice' => [
                $this->arrayVarReference('array'),
                $this->numericVar('offset'),
                $this->numericVarWithDefault('length', null),
                $this->anyWithDefault('replacement', []),
            ],
            'pop' => [$this->arrayVarReference('array')],
            'merge' => [
                [
                    self::KEY_NAME => 'values',
                    self::KEY_ACCEPTS_MANY => true,
                    self::KEY_ACCEPTS => [self::KEY_TYPE_ARRAY],
                ],
            ],
            'intersect' => [
                [
                    self::KEY_NAME => 'values',
                    self::KEY_ACCEPTS_MANY => true,
                    self::KEY_ACCEPTS => [self::KEY_TYPE_ARRAY],
                ],
            ],
            'ksort' => [
                $this->arrayVarReference('array'),
                $this->numericVarWithDefault('flags', SORT_REGULAR),
            ],
            'naturalSort' => [
                $this->arrayVarReference('array'),
            ],
            'range' => [
                [
                    self::KEY_NAME => 'start',
                    self::KEY_ACCEPTS => [
                        self::KEY_TYPE_NUMERIC,
                        self::KEY_TYPE_STRING,
                    ],
                ],
                [
                    self::KEY_NAME => 'end',
                    self::KEY_ACCEPTS => [
                        self::KEY_TYPE_NUMERIC,
                        self::KEY_TYPE_STRING,
                    ],
                ],
                $this->numericVarWithDefault('step', 1),
            ],
        ];
    }

    public function COUNT_NORMAL()
    {
        return COUNT_NORMAL;
    }

    public function COUNT_RECURSIVE()
    {
        return COUNT_RECURSIVE;
    }

    public function SORT_REGULAR()
    {
        return SORT_REGULAR;
    }

    public function SORT_NUMERIC()
    {
        return SORT_NUMERIC;
    }

    public function SORT_STRING()
    {
        return SORT_STRING;
    }

    public function SORT_LOCALE_STRING()
    {
        return SORT_LOCALE_STRING;
    }

    public function SORT_NATURAL()
    {
        return SORT_NATURAL;
    }

    public function SORT_FLAG_CASE()
    {
        return SORT_FLAG_CASE;
    }

    public function splice(&$array, $offset, $length = null, $replacement = [])
    {
        $offset = intval($offset);
        if ($length != null) {
            $length = intval($length);
        } else {
            $length = count($array) - $offset;
        }

        return array_splice($array, $offset, $length, $replacement);
    }

    public function range($start, $end, $step = 1)
    {
        return range($start, $end, $step);
    }

    public function merge($values)
    {
        return array_merge(...$values);
    }

    public function intersect($values)
    {
        return array_intersect(...$values);
    }

    public function naturalSort(&$array)
    {
        return natsort($array);
    }

    public function ksort(&$array, $flags = SORT_REGULAR)
    {
        return ksort($array, $flags);
    }

    public function shift(&$array)
    {
        return array_shift($array);
    }

    public function pop(&$array)
    {
        return array_pop($array);
    }

    public function flip($array)
    {
        return array_flip($array);
    }

    public function slice($array, $offset, $length = null, $preserve_keys = false)
    {
        $offset = intval($offset);
        if ($length != null) {
            $length = intval($length);
        }

        return array_slice($array, $offset, $length, $preserve_keys);
    }

    public function unshift(&$array, $values)
    {
        if (is_array($values)) {
            array_unshift($array, ...$values);
        } else {
            array_unshift($array, $values);
        }

        return $array;
    }

    public function push(&$array, $values)
    {
        if (is_array($values)) {
            array_push($array, ...$values);
        } else {
            array_push($array, $values);
        }

        return $array;
    }

    public function values($array)
    {
        return array_values($array);
    }

    public function keys($array)
    {
        return array_keys($array);
    }

    public function keyExists($key, $array)
    {
        return array_key_exists($key, $array);
    }

    public function min($array)
    {
        return min($array);
    }

    public function max($array)
    {
        return max($array);
    }

    public function count($array, $mode = COUNT_NORMAL)
    {
        return count($array, $mode);
    }

    public function sort(&$array, $sort_flags = SORT_REGULAR)
    {
        sort($array, $sort_flags);

        return $array;
    }

    public function asort(&$array, $sort_flags = SORT_REGULAR)
    {
        asort($array, $sort_flags);

        return $array;
    }

    public function rsort(&$array, $sort_flags = SORT_REGULAR)
    {
        rsort($array, $sort_flags);

        return $array;
    }

    public function arsort(&$array, $sort_flags = SORT_REGULAR)
    {
        arsort($array, $sort_flags);

        return $array;
    }

    public function countValues($array)
    {
        return array_count_values($array);
    }

    public function diff($arrays)
    {
        return array_diff(...$arrays);
    }

    public function reverse($array, $preserve_keys = false)
    {
        return array_reverse($array, $preserve_keys);
    }

    public function search($needle, $haystack, $strict = false)
    {
        return array_search($needle, $haystack, $strict);
    }

    public function unique($array, $sort_flags = SORT_STRING)
    {
        return array_unique($array, $sort_flags);
    }

    public function isArray($value)
    {
        return is_array($value);
    }

    public function inArray($needle, $haystack, $strict = false)
    {
        return in_array($needle, $haystack, $strict);
    }

    public function implode($delimiter, $string)
    {
        return implode($delimiter, $string);
    }

    public function explode($delimiter, $string)
    {
        return explode($delimiter, $string);
    }

    public function get($array, $key, $default = null)
    {
        if (is_numeric($key)) {
            $key = intval($key);
        }

        return Arr::get($array, $key, $default);
    }

    public function has($array, $key)
    {
        if (is_numeric($key)) {
            $key = intval($key);
        }

        return Arr::has($array, $key);
    }

    public function hasAny($array, $keys)
    {
        return Arr::hasAny($array, $keys);
    }

    public function assoc($array)
    {
        return Arr::assoc($array);
    }

    public function isAssoc($array)
    {
        return Arr::isAssoc($array);
    }

    public function dot($array, $prepend = '')
    {
        return Arr::dot($array, $prepend);
    }

    public function add($array, $key, $value)
    {
        if (is_numeric($key)) {
            $key = intval($key);
        }

        return Arr::add($array, $key, $value);
    }

    public function collapse($array)
    {
        return Arr::collapse($array);
    }

    public function crossJoin($arrays)
    {
        return Arr::crossJoin(...$arrays);
    }

    public function divide($array)
    {
        return Arr::divide($array);
    }

    public function except($array, $keys)
    {
        return Arr::except($array, $keys);
    }

    public function exists($array, $key)
    {
        if (is_numeric($key)) {
            $key = intval($key);
        }

        return Arr::exists($array, $key);
    }

    public function flatten($array, $depth = INF)
    {
        if (! is_infinite($depth)) {
            $depth = intval($depth);
        }

        return Arr::flatten($array, $depth);
    }

    public function forget(&$array, $keys)
    {
        Arr::forget($array, $keys);

        return $array;
    }

    public function only($array, $keys)
    {
        return Arr::only($array, $keys);
    }

    public function pluck($array, $value, $key = null)
    {
        if (is_numeric($key)) {
            $key = intval($key);
        }

        return Arr::pluck($array, $value, $key);
    }

    public function prepend($array, $value, $key = null)
    {
        if (is_numeric($key)) {
            $key = intval($key);
        }

        // Need to do this check. Internally, the behavior
        // of prepend uses the # of arguments supplied.
        if ($key == null) {
            return Arr::prepend($array, $value);
        }

        return Arr::prepend($array, $value, $key);
    }

    public function pull(&$array, $key, $default = null)
    {
        if (is_numeric($key)) {
            $key = intval($key);
        }

        return Arr::pull($array, $key, $default);
    }

    public function random($array, $number = null, $preserveKeys = false)
    {
        return Arr::random($array, $number, $preserveKeys);
    }

    public function set($array, $key, $value)
    {
        if (is_numeric($key)) {
            $key = intval($key);
        }

        Arr::set($array, $key, $value);

        return $array;
    }

    public function shuffle($array, $seed = null)
    {
        if (is_numeric($seed)) {
            $seed = intval($seed);
        }

        return Arr::shuffle($array, $seed);
    }

    public function query($array)
    {
        return Arr::query($array);
    }

    public function wrap($value)
    {
        return Arr::wrap($value);
    }
}
