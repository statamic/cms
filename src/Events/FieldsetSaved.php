<?php

namespace Statamic\Events;

class FieldsetSaved extends Saved
{
    public $fieldset;

    public function __construct($fieldset)
    {
        $this->fieldset = $fieldset;
    }

    public function commitMessage()
    {
        return __('Fieldset saved');
    }
}
