<?php

namespace Statamic\Fields;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Statamic\View\Antlers\Parser;

class Value implements IteratorAggregate, JsonSerializable
{
    protected $raw;
    protected $handle;
    protected $fieldtype;
    protected $parser;
    protected $context;
    protected $augmentable;

    public function __construct($value, $handle = null, $fieldtype = null, $augmentable = null)
    {
        $this->raw = $value;
        $this->handle = $handle;
        $this->fieldtype = $fieldtype;
        $this->augmentable = $augmentable;
    }

    public function raw()
    {
        return $this->raw;
    }

    public function value()
    {
        if (! $this->fieldtype) {
            return $this->raw;
        }

        $value = $this->fieldtype->augment($this->raw, $this->augmentable);

        if ($this->shouldParse()) {
            $value = $this->parse($value);
        }

        return $value;
    }

    public function __toString()
    {
        return (string) $this->value();
    }

    public function jsonSerialize($options = 0)
    {
        return $this->value();
    }

    public function getIterator()
    {
        return new ArrayIterator($this->value());
    }

    public function parseUsing(Parser $parser, $context)
    {
        $this->parser = $parser;
        $this->context = $context;

        return $this;
    }

    public function shouldParse()
    {
        if (!$this->parser || !$this->fieldtype) {
            return false;
        }

        return $this->fieldtype->config('antlers');
    }

    public function parse($value)
    {
        $value = $this->parser->parse($value, $this->context);

        // After parsing, reset the values. Wherever the parser needs to
        // parse this object, it would add itself and the contextual data.
        $this->parser = $this->context = null;

        return $value;
    }

    public function fieldtype()
    {
        return $this->fieldtype;
    }
}
