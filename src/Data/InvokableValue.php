<?php

namespace Statamic\Data;

use Statamic\Data\Concerns\ResolvesValues;
use Statamic\Fields\Value;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;

class InvokableValue extends Value
{
    use ResolvesValues;

    protected $methodTarget = null;
    protected bool $hasResolved = false;
    protected string $methodName;
    protected bool $proxyThroughAugmented = false;
    protected $resolvedValueInstance = null;

    public function setInvokableDetails(string $method, bool $proxyCall, $target): self
    {
        $this->proxyThroughAugmented = $proxyCall;
        $this->methodName = $method;
        $this->methodTarget = $target;

        return $this;
    }

    protected function resolve()
    {
        if ($this->hasResolved) {
            return;
        }

        if ($this->methodTarget == null) {
            $this->hasResolved = true;

            return;
        }

        $curIsolationState = GlobalRuntimeState::$requiresRuntimeIsolation;

        GlobalRuntimeState::$requiresRuntimeIsolation = true;
        if ($this->proxyThroughAugmented && method_exists($this->methodTarget, 'getAugmentedMethodValue')) {
            $this->raw = $this->methodTarget->getAugmentedMethodValue($this->methodName);

            if (! $this->raw instanceof Value) {
                // Replicate previous behavior of not having
                // a field set if the method call did not
                // return a Value instance.
                $this->fieldtype = null;
            } else {
                // Store the original Value instance, if we have it.
                $this->resolvedValueInstance = $this->raw;

                // Shift some values around.
                $this->fieldtype = $this->raw->fieldtype();
                $this->raw = $this->raw->raw();
            }
        } elseif (! $this->proxyThroughAugmented) {
            $this->raw = $this->methodTarget->{$this->methodName}();
        }

        $this->methodTarget = null;

        $this->hasResolved = true;

        GlobalRuntimeState::$requiresRuntimeIsolation = $curIsolationState;
    }

    public function materialize()
    {
        $this->resolve();

        if ($this->resolvedValueInstance != null) {
            return $this->resolvedValueInstance;
        }

        return $this->toValue();
    }
}
