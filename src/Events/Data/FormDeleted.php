<?php

namespace Statamic\Events\Data;

class FormDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Form deleted');
    }
}
