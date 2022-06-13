<?php

namespace Statamic\Assets;

/**
 * @deprecated
 */
class Dimensions extends Attributes
{
    public function get()
    {
        $attrs = parent::get();

        return [$attrs['width'] ?? null, $attrs['height'] ?? null];
    }

    public function width()
    {
        return array_get($this->get(), 0);
    }

    public function height()
    {
        return array_get($this->get(), 1);
    }
}
