<?php

namespace Statamic\Fieldtypes;

use Statamic\Entries\Entry;
use Statamic\Fields\Field;
use Statamic\Fields\Validator;

trait AddsEntryValidationReplacements
{
    protected function addEntryValidationReplacements(Field $field, Validator $rules): Validator
    {
        $fieldParent = $field->parent();

        if (! $fieldParent instanceof Entry) {
            return $rules;
        }

        return $rules->withReplacements([
            'id' => $fieldParent->id(),
            'collection' => $fieldParent->collection()->handle(),
            'site' => $fieldParent->locale(),
        ]);
    }
}
