<?php

namespace Statamic\Data;

use Statamic\Contracts\Data\Augmented;
use Statamic\Fields\Value;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;

class InvokableValue extends Value
{
    private $methodTarget;
    private bool $hasResolved = false;
    private string $methodName;
    private ?Value $resolvedValueInstance = null;

    public function setInvokableDetails(string $method, $target): self
    {
        $this->methodName = $method;
        $this->methodTarget = $target;

        return $this;
    }

    protected function resolve()
    {
        if ($this->hasResolved) {
            return;
        }

        $curIsolationState = GlobalRuntimeState::$requiresRuntimeIsolation;
        GlobalRuntimeState::$requiresRuntimeIsolation = true;

        if ($this->methodTarget instanceof Augmented) {
            $this->resolveAugmented();
        } else {
            $this->resolveAugmentable();
        }

        $this->hasResolved = true;

        GlobalRuntimeState::$requiresRuntimeIsolation = $curIsolationState;
    }

    private function resolveAugmented()
    {
        $this->raw = $this->methodTarget->getAugmentedMethodValue($this->methodName);

        if ($this->raw instanceof Value) {
            // Store the original Value instance, if we have it.
            $this->resolvedValueInstance = $this->raw;

            // Shift some values around.
            $this->fieldtype = $this->raw->fieldtype();
            $this->raw = $this->raw->raw();
        } else {
            // Replicate previous behavior of not having
            // a field set if the method call did not
            // return a Value instance.
            $this->fieldtype = null;
        }
    }

    private function resolveAugmentable()
    {
        $this->raw = $this->methodTarget->{$this->methodName}();
    }

    public function materialize()
    {
        $this->resolve();

        if ($this->resolvedValueInstance != null) {
            return $this->resolvedValueInstance;
        }

        return new Value($this->raw, $this->handle, $this->fieldtype, $this->augmentable, $this->shallow);
    }
}
