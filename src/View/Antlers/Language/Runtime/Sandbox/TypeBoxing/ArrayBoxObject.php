<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox\TypeBoxing;

use Illuminate\Support\Traits\Macroable;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class ArrayBoxObject
{
    use Macroable, AntlersBoxedStandardMethods;

    protected $value = [];

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function length()
    {
        return count($this->value);
    }

    public function count()
    {
        return count($this->value);
    }

    public function join($delimiter)
    {
        return implode($delimiter, $this->value);
    }

    public function implode($delimiter)
    {
        return implode($delimiter, $this->value);
    }

    public function sentenceList($glue = 'and', $oxford_comma = true)
    {
        return Str::makeSentenceList($this->value, $glue, $oxford_comma);
    }

    public function merge(...$values)
    {
        return array_merge($this->value, $values);
    }

    public function intersect(...$values)
    {
        return array_intersect($this->value, $values);
    }

    public function naturalSort()
    {
        natsort($this->value);

        return $this->value;
    }

    public function keySort()
    {
        ksort($this->value);

        return $this->value;
    }

    public function shift()
    {
        array_shift($this->value);

        return $this->value;
    }

    public function first()
    {
        return array_shift($this->value);
    }

    public function pop()
    {
        array_pop($this->value);

        return $this->value;
    }

    public function last()
    {
        return array_pop($this->value);
    }

    public function push($values)
    {
        if (is_array($values)) {
            array_push($this->value, ...$values);

            return $this->value;
        }

        array_push($this->value, $values);

        return $this->value;
    }

    public function values()
    {
        return array_values($this->value);
    }

    public function keys()
    {
        return array_keys($this->value);
    }

    public function keyExists($key)
    {
        return array_key_exists($key, $this->value);
    }

    public function min()
    {
        return min($this->value);
    }

    public function max()
    {
        return max($this->value);
    }

    public function asort($flags = SORT_REGULAR)
    {
        asort($this->value, $flags);

        return $this->value;
    }

    public function rsort($flags = SORT_REGULAR)
    {
        rsort($this->value, $flags);

        return $this->value;
    }

    public function countValues()
    {
        return array_count_values($this->value);
    }

    public function diff($arrays)
    {
        return array_diff($this->value, ...$arrays);
    }

    public function reverse($preserve_keys = false)
    {
        return array_reverse($this->value, $preserve_keys);
    }

    public function search($needle, $strict = false)
    {
        return array_search($needle, $this->value, $strict);
    }

    public function unique($flags = SORT_STRING)
    {
        return array_unique($this->value, $flags);
    }

    public function inArray($needle, $strict = false)
    {
        return in_array($needle, $this->value, $strict);
    }

    public function get($key, $default = null)
    {
        return Arr::get($this->value, $key, $default);
    }

    public function has($key)
    {
        return Arr::has($this->value, $key);
    }

    public function hasAny($keys)
    {
        return Arr::hasAny($this->value, $keys);
    }

    public function assoc()
    {
        return Arr::assoc($this->value);
    }

    public function isAssoc()
    {
        return Arr::isAssoc($this->value);
    }

    public function dot($prepend = '')
    {
        return Arr::dot($this->value, $prepend);
    }

    public function add($key, $value)
    {
        return Arr::add($this->value, $key, $value);
    }

    public function collapse()
    {
        return Arr::collapse($this->value);
    }

    public function crossJoin($arrays)
    {
        return Arr::crossJoin($this->value, ...$arrays);
    }

    public function divide()
    {
        return Arr::divide($this->value);
    }

    public function except($keys)
    {
        return Arr::except($this->value, $keys);
    }

    public function exists($key)
    {
        return Arr::exists($this->value, $key);
    }

    public function flatten($depth = INF)
    {
        return Arr::flatten($this->value, $depth);
    }

    public function forget($keys)
    {
        Arr::forget($this->value, $keys);

        return $this->value;
    }

    public function only($keys)
    {
        return Arr::only($this->value, $keys);
    }

    public function pluck($value, $key = null)
    {
        return Arr::pluck($this->value, $value, $key);
    }

    public function prepend($value, $key = null)
    {
        if ($key == null) {
            return Arr::prepend($this->value, $value);
        }

        return Arr::prepend($this->value, $value, $key);
    }

    public function pull($key, $default = null)
    {
        return Arr::pull($this->value, $key, $default);
    }

    public function random($number = null, $preserveKeys = false)
    {
        return Arr::random($this->value, $number, $preserveKeys);
    }

    public function set($key, $value)
    {
        Arr::set($this->value, $key, $value);

        return $this->value;
    }

    public function shuffle($seed = null)
    {
        return Arr::shuffle($this->value, $seed);
    }

    public function query()
    {
        return Arr::query($this->value);
    }

    public function wrap()
    {
        return Arr::wrap($this->value);
    }
}
