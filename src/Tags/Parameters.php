<?php

namespace Statamic\Tags;

use Statamic\Facades\Antlers;
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
            // Values in parameters prefixed with a colon should be treated as the corresponding field's value in the context.
            if (Str::startsWith($key, ':')) {
                $key = substr($key, 1);
                $value = Antlers::parser()->getVariable($value, $context->all());
            }

            if ($value instanceof Value) {
                $value = $value->value();
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
}
