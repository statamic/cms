<?php

namespace Statamic\Data;

use Statamic\Fields\Value;

class DeferredValue extends Value
{
    protected $augmentedReference = null;
    protected $hasResolved = false;

    protected function resolve()
    {
        if ($this->hasResolved) {
            return;
        }

        $this->hasResolved = true;

        if ($this->augmentedReference == null) {
            return;
        }

        $this->raw = $this->augmentedReference->getFromData($this->handle);
    }

    public function withAugmentedReference($instance)
    {
        $this->augmentedReference = $instance;

        return $this;
    }

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
}
