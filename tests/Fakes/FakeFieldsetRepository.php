<?php

namespace Tests\Fakes;

use Statamic\Fields\Fieldset;
use Statamic\Support\Arr;

class FakeFieldsetRepository
{
    protected $fieldsets = [];

    public function find(string $handle): ?Fieldset
    {
        if ($fieldset = Arr::get($this->fieldsets, $handle)) {
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
