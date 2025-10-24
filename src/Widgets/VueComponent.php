<?php

namespace Statamic\Widgets;

use Illuminate\Contracts\Support\Arrayable;

class VueComponent implements Arrayable
{
    public function __construct(private string $name, private array $props = [])
    {
    }

    public static function render($name, $props)
    {
        return new static($name, $props);
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'props' => $this->props,
        ];
    }
}
