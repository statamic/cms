<?php

namespace Statamic\Tags;

use Statamic\Fields\Value;
use Statamic\View\Antlers\Parser;

class Context extends ArrayAccessor
{
    protected $parser;

    public function setParser(Parser $parser)
    {
        $this->parser = $parser;

        return $this;
    }

    public function get($key, $default = null)
    {
        $value = parent::get($key, $default);

        if ($value instanceof Value) {
            $value = $value->parseUsing($this->parser, $this->items)->value();
        }

        return $value;
    }

    public function raw($key, $default = null)
    {
        $value = parent::get($key, $default);

        return $value instanceof Value ? $value->raw() : $value;
    }

    public function value($key, $default = null)
    {
        $value = parent::get($key, $default);

        if (! $value instanceof Value) {
            $value = new Value($value);
        }

        return $value->parseUsing($this->parser, $this->items);
    }
}
