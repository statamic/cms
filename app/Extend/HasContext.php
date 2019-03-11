<?php

namespace Statamic\Extend;

use Statamic\API\Arr;
use Statamic\Fields\Value;

trait HasContext
{
    public $context;

    public function getContext($key, $fallback = null)
    {
        $value = Arr::get($this->context, $key, $fallback);

        if ($value instanceof Value) {
            $value = $value->parseUsing($this->parser, $this->context)->value();
        }

        return $value;
    }
}
