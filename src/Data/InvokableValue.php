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
        $value = $this->methodTarget->getAugmentedMethodValue($this->methodName);

        if ($value instanceof Value) {
            $this->resolvedValueInstance = $value;
            $this->fieldtype = $value->fieldtype();
            $this->raw = $value->raw();
        } else {
            // If the method doesn't return a value instance, it's intentionally returning just a raw value.
            // For example, the `uri` method, which is just a string.
            // Or, a method that has the same name as a blueprint field, like `authors`.
            // We'll remove the fieldtype, otherwise when value() gets run, it would try to augment it, and we don't want that.
            $this->raw = $value;
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
