<?php

namespace Statamic\Events;

class FieldsetSaved extends Saved
{
    public function commitMessage()
    {
        return __('Fieldset saved');
    }
}
