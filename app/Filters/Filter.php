<?php

namespace Statamic\Filters;

use Statamic\Extend\HasTitle;
use Statamic\Extend\HasHandle;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Extend\RegistersItself;

abstract class Filter implements Arrayable
{
    use HasTitle, HasHandle, RegistersItself;

    protected static $binding = 'filters';
    protected $context;

    public function required()
    {
        return false;
    }

    public function visibleTo($key)
    {
        return true;
    }

    public function context($context)
    {
        $this->context = $context;

        return $this;
    }

    public function options()
    {
        return [];
    }

    public function extra()
    {
        return [];
    }

    public function toArray()
    {
        return [
            'handle' => $this->handle(),
            'title' => $this->title(),
            'options' => format_input_options($this->options()),
            'extra' => $this->extra(),
            'required' => $this->required(),
        ];
    }
}
