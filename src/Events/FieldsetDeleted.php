<?php

namespace Statamic\Events;

class FieldsetDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Fieldset deleted');
    }
}
