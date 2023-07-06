<?php

namespace Statamic\Tags;

use Statamic\Support\Arr;
use Statamic\View\State\ResetsState;

class Rotate extends Tags implements ResetsState
{
    protected static $aliases = ['switch'];
    protected static $counts = [];

    public static function resetStaticState()
    {
        self::$counts = [];
    }

    /**
     * The {{ rotate }} tag.
     *
     * @return string
     */
    public function index()
    {
        if (! $between = $this->params->get('between')) {
            // No 'between' parameter? Why are you using this tag?
            return null;
        }

        // Expanded mode lets you set the number of times a value is repeated
        $expanded = strstr($between, ':');

        // Create a unique hash based on the parameters to provide users
        // a method of using multiple switch tags in a single template.
        $hash = md5(implode(',', $this->params->all()));

        if (! isset(static::$counts[$hash])) {
            static::$counts[$hash] = 0;
        }

        $vars = Arr::explodeOptions($between);

        if ($expanded) {
            $vars = $this->expand($vars);
        }

        $switch = $vars[static::$counts[$hash] % count($vars)];

        static::$counts[$hash]++;

        return $switch;
    }

    /**
     * Expand switch values with : colon syntax to let you set the
     * number of times you want a specific value repeated.
     *
     * @param  array  $values
     * @return array
     */
    private function expand($values)
    {
        $vars = [];

        foreach ($values as $initial_value) {
            $bits = explode(':', $initial_value);

            $value_bit = Arr::get($bits, 0);
            $count_bit = Arr::get($bits, 1);

            $value = is_numeric($count_bit) ? $value_bit : $initial_value;
            $count = is_numeric($count_bit) ? $count_bit : 1;

            for ($i = 1; $i <= $count; $i++) {
                $vars[] = $value;
            }
        }

        return $vars;
    }
}
