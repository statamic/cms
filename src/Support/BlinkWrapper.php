<?php

namespace Statamic\Support;

use Spatie\Blink\Blink as SpatieBlink;

class BlinkWrapper extends SpatieBlink
{
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    public function get(string $key, $default = null)
    {
        return array_key_exists($key, $this->values) ? $this->values[$key] : $default;
    }
}
