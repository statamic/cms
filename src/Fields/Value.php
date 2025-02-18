<?php

namespace Statamic\Fields;

use ArrayAccess;
use ArrayIterator;
use Illuminate\Support\Collection;
use IteratorAggregate;
use JsonSerializable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\View\Antlers\Parser;
use Statamic\Facades\Compare;
use Statamic\Support\Str;
use Statamic\View\Antlers\Language\Parser\DocumentTransformer;
use Traversable;

class Value implements ArrayAccess, IteratorAggregate, JsonSerializable
{
    private $resolver;
    protected $raw;
    protected $handle;
    protected $fieldtype;
    protected $augmentable;
    protected $shallow = false;

    public function __construct($value, $handle = null, $fieldtype = null, $augmentable = null, $shallow = false)
    {
        if ($value instanceof \Closure) {
            $this->resolver = $value;
        } else {
            $this->raw = $value;
        }

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
        $this->resolve();

        return $this->raw;
    }

    public function resolve()
    {
        if (! $this->resolver) {
            return $this;
        }

        $callback = $this->resolver;
        $value = $callback($this);
        $this->resolver = null;

        if ($value instanceof Value) {
            $this->fieldtype = $value->fieldtype();
            $this->raw = $value->raw();
        } else {
            $this->raw = $value;
        }

        return $this;
    }

    public function value()
    {
        $this->resolve();

        $raw = $this->raw;

        if (! $this->fieldtype) {
            return $raw;
        }

        if ($raw === null) {
            $raw = $this->fieldtype->field()?->defaultValue() ?? null;
        }

        $value = $this->shallow
            ? $this->fieldtype->shallowAugment($raw)
            : $this->fieldtype->augment($raw);

        return $value;
    }

    private function iteratorValue()
    {
        $value = $this->value();

        if (Compare::isQueryBuilder($value)) {
            $value = $value->get();
        }

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

        if (Compare::isQueryBuilder($value)) {
            $value = $value->get();
        }

        if ($value instanceof Augmentable || $value instanceof Collection) {
            $value = $value->toArray();
        }

        return $value;
    }

    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        $value = $this->iteratorValue();

        return $value instanceof Traversable ? $value : new ArrayIterator($value);
    }

    public function shouldParseAntlers()
    {
        $this->resolve();

        return $this->fieldtype && $this->fieldtype->config('antlers');
    }

    public function antlersValue(Parser $parser, $variables)
    {
        $value = $this->value();
        $shouldParseAntlers = $this->shouldParseAntlers();

        if ($value instanceof ArrayableString && $shouldParseAntlers) {
            $value = (string) $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        if ($shouldParseAntlers) {
            $value = (new DocumentTransformer())->correct($value);

            return $parser->parse($value, $variables);
        }

        if (Str::contains($value, '{')) {
            return $parser->valueWithNoparse($value);
        }

        return $value;
    }

    public function field()
    {
        $this->resolve();

        return $this->fieldtype->field();
    }

    public function fieldtype()
    {
        $this->resolve();

        return $this->fieldtype;
    }

    public function setFieldtype($fieldtype)
    {
        $this->fieldtype = $fieldtype;
    }

    public function augmentable()
    {
        $this->resolve();

        return $this->augmentable;
    }

    public function setAugmentable($augmentable)
    {
        $this->augmentable = $augmentable;
    }

    public function handle()
    {
        return $this->handle;
    }

    public function shallow()
    {
        $this->resolve();

        return new static($this->raw, $this->handle, $this->fieldtype, $this->augmentable, true);
    }

    public function isRelationship(): bool
    {
        $this->resolve();

        return optional($this->fieldtype)->isRelationship() ?? false;
    }

    public function __serialize(): array
    {
        $this->resolve();

        return get_object_vars($this);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->value()->{$name}(...$arguments);
    }

    public function __isset($key)
    {
        return isset($this->value()?->{$key});
    }

    public function __get($key)
    {
        return $this->value()?->{$key} ?? null;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists(mixed $offset)
    {
        $value = $this->value();

        if (! is_array($value)) {
            return false;
        }

        return array_key_exists($offset, $value);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $offset)
    {
        return $this->value()[$offset];
    }

    #[\ReturnTypeWillChange]
    public function offsetSet(mixed $offset, mixed $value)
    {

    }

    #[\ReturnTypeWillChange]
    public function offsetUnset(mixed $offset)
    {
    }
}
