<?php

namespace Statamic\Fields;

use ArrayIterator;
use Illuminate\Support\Collection;
use IteratorAggregate;
use JsonSerializable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Support\Str;
use Statamic\View\Antlers\Parser;

class Value implements IteratorAggregate, JsonSerializable
{
    protected $raw;
    protected $handle;
    protected $fieldtype;
    protected $augmentable;
    protected $shallow = false;

    public function __construct($value, $handle = null, $fieldtype = null, $augmentable = null, $shallow = false)
    {
        $this->raw = $value;
        $this->handle = $handle;
        $this->fieldtype = $fieldtype;
        $this->augmentable = $augmentable;
        $this->shallow = $shallow;

        if ($fieldtype && $fieldtype->field()) {
            $this->fieldtype->field()->setParent($augmentable);
        }
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

        $value = $this->shallow
            ? $this->fieldtype->shallowAugment($this->raw)
            : $this->fieldtype->augment($this->raw);

        return $value;
    }

    public function __toString()
    {
        return (string) $this->value();
    }

    public function jsonSerialize($options = 0)
    {
        $value = $this->value();

        if ($value instanceof Augmentable || $value instanceof Collection) {
            $value = $value->toAugmentedArray();
        }

        return $value;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->value());
    }

    public function shouldParseAntlers()
    {
        return $this->fieldtype && $this->fieldtype->config('antlers');
    }

    public function antlersValue(Parser $parser, $variables)
    {
        $value = $this->value();

        if (! is_string($value)) {
            return $value;
        }

        if ($this->shouldParseAntlers()) {
            return $parser->parse($value, $variables);
        }

        if (Str::contains($value, '{')) {
            return $parser->extractNoparse(str_replace('{{', '@{{', $value));
        }

        return $value;
    }

    public function field()
    {
        return $this->fieldtype->field();
    }

    public function fieldtype()
    {
        return $this->fieldtype;
    }

    public function augmentable()
    {
        return $this->augmentable;
    }

    public function handle()
    {
        return $this->handle;
    }

    public function shallow()
    {
        return new static($this->raw, $this->handle, $this->fieldtype, $this->augmentable, true);
    }
}
