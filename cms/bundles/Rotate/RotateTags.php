<?php

namespace Statamic\Addons\Rotate;

use Statamic\API\Helper;
use Statamic\Extend\Tags;

class RotateTags extends Tags
{
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
        $expanded_mode = strstr($between, ':');

        // Create a unique hash based on the parameters to provide users
        // a method of using multiple switch tags in a single template.
        $hash = md5(implode(',', $this->parameters));

        if (! $this->blink->exists($hash)) {
            // Instance counter
            $this->blink->put($hash, 0);
        }

        $switch_vars = Helper::explodeOptions($between);

        if ($expanded_mode) {
            $switch_vars = $this->expand($switch_vars);
        }

        $switch = $switch_vars[($this->blink->get($hash)) % count($switch_vars)];

        $this->blink->increment($hash);

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
        $switch_vars = [];

        foreach ($values as $key => $value) {
            $repeating_values = explode(':', $value);
            $repeat_count = array_get($repeating_values, 1, 1);

            for ($i = 1; $i <= $repeat_count; $i++) {
                $switch_vars[] = $repeating_values[0];
            }
        }

        return $switch_vars;
    }
}
