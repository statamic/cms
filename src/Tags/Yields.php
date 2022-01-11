<?php

namespace Statamic\Tags;

use Facades\Statamic\View\Cascade;

class Yields extends Tags
{
    protected static $aliases = ['yield'];

    public function __call($method, $args)
    {
        $name = explode(':', $this->tag)[1];

        if ($yield = $this->getYieldedValue($name)) {
            return $yield;
        }

        if ($fallback = $this->params->get('or')) {
            return $fallback;
        }

        return $this->isPair ? $this->parse() : null;
    }

    private function getYieldedValue($name)
    {
        // First try to get it from the Illuminate view factory, which may have a section
        // in there if it was added via a Blade template using the `@section` directive.
        if ($value = view()->shared('__env')->yieldContent($name)) {
            return $value;
        }

        // Then try to get it from the cascade, which the `section` tag
        // stores its contents in when used in an Antlers template.
        if ($value = Cascade::instance()->sections()->get($name)) {
            return $value;
        }
    }
}
