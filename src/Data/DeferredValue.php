<?php

namespace Statamic\Data;

use Statamic\Contracts\Data\Augmented;
use Statamic\Data\Concerns\ResolvesValues;
use Statamic\Fields\Value;

class DeferredValue extends Value
{
    use ResolvesValues;

    protected Augmented $augmentedReference;
    protected $hasResolved = false;

    protected function resolve()
    {
        if ($this->hasResolved) {
            return;
        }

        $this->raw = $this->augmentedReference->getFromData($this->handle);

        $this->hasResolved = true;
    }

    public function withAugmentedReference(Augmented $instance)
    {
        $this->augmentedReference = $instance;

        return $this;
    }

    public function shallow()
    {
        return parent::shallow()->withAugmentedReference($this->augmentedReference);
    }
}
