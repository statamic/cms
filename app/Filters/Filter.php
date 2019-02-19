<?php

namespace Statamic\Filters;

use Statamic\API\Str;
use Statamic\Extend\HasTitle;
use Statamic\Extend\HasHandle;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Extend\RegistersItself;

abstract class Filter implements Arrayable
{
    use HasTitle, HasHandle, RegistersItself;

    protected static $binding = 'filters';

    public function required()
    {
        return false;
    }

    public function visible($key, $context)
    {
        return false;
    }

    public function toArray()
    {
        return [
            'handle' => $this->handle(),
            'title' => $this->title(),
            'options' => format_input_options($this->options()),
            'required' => $this->required(),
        ];
    }
}
