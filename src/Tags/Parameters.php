<?php

namespace Statamic\Tags;

use Statamic\Fields\Value;
use Statamic\Support\Str;

class Parameters extends ArrayAccessor
{
    public static function make($items = [], $context = null)
    {
        if (! $context) {
            throw new \InvalidArgumentException('A Context object is expected.');
        }

        $items = collect($items)->mapWithKeys(function ($value, $key) use ($context) {
            // Values in parameters prefixed with a colon should be treated as the corresponding
            // field's value in the context. If it doesn't exist, the value remains the literal.
            if (Str::startsWith($key, ':')) {
                $key = substr($key, 1);
                $value = $context->get($value, $value);
            }

            if ($value === 'true') {
                $value = true;
            }

            if ($value === 'false') {
                $value = false;
            }

            return [$key => $value];
        })->all();

        return parent::make($items);
    }

    public function get($keys, $default = null)
    {
        $value = parent::get($keys, $default);

        return $value instanceof Value ? $value->value() : $value;
    }
}
