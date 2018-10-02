<?php

namespace Tests\Fakes;

use Statamic\Fields\Fieldset;

class FakeFieldsetRepository
{
    protected $fieldsets = [];

    public function find(string $handle): ?Fieldset
    {
        return $this->fieldsets[$handle] ?? null;
    }

    public function save(Fieldset $fieldset)
    {
        $this->fieldsets[$fieldset->handle()] = $fieldset;
    }

    public function all()
    {
        return $this->fieldsets;
    }
}
