<?php

namespace Statamic\Events\Data;

class FieldsetSaved extends Saved
{
    public function commitMessage()
    {
        return __('Fieldset saved');
    }
}
