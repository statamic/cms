<?php

namespace Statamic\Support;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A neutral {name, props} DTO for rendering a Vue component registered
 * client-side via Statamic.component(), e.g. by form connections and widgets.
 *
 * @phpstan-consistent-constructor
 */
class VueComponent implements Arrayable
{
    public function __construct(private string $name, private array $props = [])
    {
    }

    public static function render($name, $props = [])
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
