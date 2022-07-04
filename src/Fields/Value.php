<?php

namespace Statamic\Fields;

use ArrayIterator;
use Illuminate\Support\Collection;
use IteratorAggregate;
use JsonSerializable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\Support\Str;
use Statamic\View\Antlers\Language\Parser\DocumentTransformer;

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

    #[\ReturnTypeWillChange]
    public function jsonSerialize($options = 0)
    {
        $value = $this->value();

        if ($value instanceof Augmentable || $value instanceof Collection) {
            $value = $value->toAugmentedArray();
        }

        return $value;
    }

    #[\ReturnTypeWillChange]
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
        $shouldParseAntlers = $this->shouldParseAntlers();

        if ($value instanceof  ArrayableString && $shouldParseAntlers) {
            $value = (string) $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        if ($shouldParseAntlers) {
            if (config('statamic.antlers.version') === 'runtime') {
                $value = (new DocumentTransformer())->correct($value);
            }

            return $parser->parse($value, $variables);
        }

        if (Str::contains($value, '{')) {
            return $parser->valueWithNoparse($value);
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

    public function isRelationship(): bool
    {
        return optional($this->fieldtype)->isRelationship() ?? false;
    }
}
