<?php

namespace Statamic\Tags;

use Statamic\API\Str;

class Parameters extends ArrayAccessor
{
    public function __construct($parameters, $context)
    {
        parent::__construct($this->initialize($parameters, $context));
    }

    protected function initialize($parameters, $context)
    {
        return collect($parameters)->mapWithKeys(function ($value, $key) use ($context) {
            // Values in parameters prefixed with a colon should be treated as the corresponding
            // field's value in the context. If it doesn't exist, the value remains the literal.
            if (Str::startsWith($key, ':')) {
                $key = substr($key, 1);
                $value = $context[$value] ?? $value;
            }

            if ($value === 'true') {
                $value = true;
            }

            if ($value === 'false') {
                $value = false;
            }

            return [$key => $value];
        })->all();
    }
}