<?php

namespace Statamic\Tags;

use Statamic\Support\Arr;
use Statamic\Tags\Tags;

class Rotate extends Tags
{
    protected static $aliases = ['switch'];
    protected static $counts = [];

    /**
     * The {{ rotate }} tag
     *
     * @return string
     */
    public function index()
    {
        if (! $between = $this->get('between')) {
            // No 'between' parameter? Why are you using this tag?
            return null;
        }

        // Expanded mode lets you set the number of times a value is repeated
        $expanded = strstr($between, ':');

        // Create a unique hash based on the parameters to provide users
        // a method of using multiple switch tags in a single template.
        $hash = md5(implode(',', $this->parameters->all()));

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
     * @param array $values
     * @return array
     */
    private function expand($values)
    {
        $vars = [];

        foreach ($values as $value) {
            $repeatingValues = explode(':', $value);
            $count = Arr::get($repeatingValues, 1, 1);

            for ($i = 1; $i <= $count; $i++) {
                $vars[] = $repeatingValues[0];
            }
        }

        return $vars;
    }
}
