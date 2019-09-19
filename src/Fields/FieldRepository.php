<?php

namespace Statamic\Fields;

use Statamic\Facades\YAML;

class FieldRepository
{
    protected $fieldsets;

    public function __construct(FieldsetRepository $fieldsets)
    {
        $this->fieldsets = $fieldsets;
    }

    public function find(string $field): ?Field
    {
        if (! str_contains($field, '.')) {
            return null;
        }

        list($fieldset, $handle) = explode('.', $field);

        if (! $fieldset = $this->fieldsets->find($fieldset)) {
            return null;
        }

        return $fieldset->field($handle);
    }
}
