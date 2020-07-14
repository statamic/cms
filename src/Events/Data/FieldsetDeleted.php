<?php

namespace Statamic\Events\Data;

class FieldsetDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Fieldset deleted');
    }
}
