<?php

namespace Statamic\Data\Concerns;

use Statamic\Fields\Value;

trait ResolvesValues
{
    abstract protected function resolve();

    public function materialize()
    {
        $this->resolve();

        return $this->toValue();
    }

    protected function toValue()
    {
        return new Value($this->raw, $this->handle, $this->fieldtype, $this->augmentable, $this->shallow);
    }

    public function raw()
    {
        $this->resolve();

        return parent::raw();
    }

    public function value()
    {
        $this->resolve();

        return parent::value();
    }

    public function shallow()
    {
        $this->resolve();

        return parent::shallow();
    }

    public function isRelationship(): bool
    {
        $this->resolve();

        return parent::isRelationship();
    }
}
