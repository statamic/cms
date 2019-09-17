<?php

namespace Tests\Fakes;

use Statamic\Fields\Fieldset;

class FakeFieldsetRepository
{
    protected $fieldsets = [];

    public function find(string $handle): ?Fieldset
    {
        if ($fieldset = array_get($this->fieldsets, $handle)) {
            // Return a clone so that modifications to the object will only be updated when saving.
            return clone $fieldset;
        }

        return null;
    }

    public function save(Fieldset $fieldset)
    {
        $this->fieldsets[$fieldset->handle()] = $fieldset;
    }

    public function all()
    {
        return collect($this->fieldsets);
    }
}
