<?php

namespace Statamic\Fieldtypes;

use Statamic\Entries\Entry;
use Statamic\Fields\Field;
use Statamic\Fields\Validator;

/**
 * TODO
 * This allows Grid/Replicator/Bard fields to add validation replacements.
 * It adds the same replacements that get added in EntriesController@update.
 * Ideally those would get passed down into the field automatically somehow,
 * so this can be considered a workaround until that happens.
 */
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
