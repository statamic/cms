<?php

namespace Statamic\Filters;

use Statamic\API\Str;
use Illuminate\Contracts\Support\Arrayable;

abstract class Filter implements Arrayable
{
    public static function title()
    {
        return static::$title
            ?? Str::humanize(str_replace((new \ReflectionClass(static::class))->getNamespaceName().'\\', '', static::class));
    }

    public static function handle()
    {
        return static::$handle ?? snake_case(static::title());
    }

    public function required()
    {
        return false;
    }

    public function visible($key)
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
