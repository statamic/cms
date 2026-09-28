<?php

namespace Statamic\Data;

use Statamic\Fields\Fields;

class NestedFieldUpdater
{
    public function __construct(
        private DataReferenceUpdater $updater,
        private string $fieldKey
    ) {
    }

    /**
     * Process nested fields for reference updates at the given dotted prefix.
     */
    public function update(Fields $fields, string $dottedPrefix = ''): void
    {
        $prefix = rtrim($this->fieldKey.'.'.$dottedPrefix, '.').'.';

        $this->updater->processNestedFields($fields, $prefix);
    }
}
