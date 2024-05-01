<?php

namespace Statamic\Data;

use Statamic\Contracts\Data\Augmented;
use Statamic\Data\Concerns\ResolvesValues;
use Statamic\Fields\Field;
use Statamic\Fields\Value;

class TransientValue extends Value
{
    use ResolvesValues;

    private ?Augmented $augmentedReference = null;
    private ?Field $fieldReference;
    private bool $hasResolved = false;

    public function withAugmentationReferences(Augmented $augmented, $field)
    {
        $this->augmentedReference = $augmented;
        $this->fieldReference = $field;

        return $this;
    }

    protected function resolve()
    {
        if ($this->hasResolved) {
            return;
        }

        // Calling ->get() directly will materialize any other deferred values for us.
        $value = $this->augmentedReference->get($this->handle, $this->fieldReference?->fieldtype());

        $this->raw = $value->raw();
        $this->fieldtype = $value->fieldtype();
        $this->augmentable = $value->augmentable();

        $this->hasResolved = true;
    }

    public function shallow()
    {
        return parent::shallow()->withAugmentationReferences($this->augmentedReference, $this->fieldReference);
    }
}
