<?php

namespace Tests\Fakes;

use Illuminate\Support\Collection;
use Statamic\Fields\Fieldset;
use Statamic\Fields\FieldsetRepository;
use Statamic\Support\Arr;

class FakeFieldsetRepository extends FieldsetRepository
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

    public function all(): Collection
    {
        return collect($this->fieldsets);
    }
}
