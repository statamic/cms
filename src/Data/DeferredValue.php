<?php

namespace Statamic\Data;

use Statamic\Data\Concerns\ResolvesValues;
use Statamic\Fields\Value;

class DeferredValue extends Value
{
    use ResolvesValues;

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
}
