<?php

namespace Statamic\Events;

class FieldsetDeleted extends Deleted
{
    public $fieldset;

    public function __construct($fieldset)
    {
        $this->fieldset = $fieldset;
    }

    public function commitMessage()
    {
        return __('Fieldset deleted');
    }
}
