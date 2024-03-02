<?php

namespace Statamic\Data;

use Statamic\Data\Concerns\ResolvesValues;
use Statamic\Fields\Value;

class TransientValue extends Value
{
    use ResolvesValues;

    protected $augmentedReference;
    protected $fieldReference;
    protected $hasResolved = false;

    public function withAugmentationReferences($augmentable, $field)
    {
        $this->augmentedReference = $augmentable;
        $this->fieldReference = $field;

        return $this;
    }

    protected function resolve()
    {
        if ($this->hasResolved) {
            return;
        }

        $this->hasResolved = true;

        if ($this->augmentedReference === null) {
            return;
        }

        // Calling ->get() directly will materialize any other deferred values for us.
        $value = $this->augmentedReference->get($this->handle, $this->fieldReference?->fieldtype());

        if ($value === null) {
            return;
        }

        $this->raw = $value->raw();
        $this->fieldtype = $value->fieldtype();
        $this->augmentable = $value->augmentable();
    }
}
