<?php

namespace Statamic\Fields;

use Statamic\Support\Str;

class FieldRepository
{
    protected $fieldsets;

    public function __construct(FieldsetRepository $fieldsets)
    {
        $this->fieldsets = $fieldsets;
    }

    public function find(string $field): ?Field
    {
        if (! Str::contains($field, '.')) {
            return null;
        }

        [$fieldset, $handle] = explode('.', $field);

        if (! $fieldset = $this->fieldsets->find($fieldset)) {
            return null;
        }

        return $fieldset->field($handle);
    }
}
