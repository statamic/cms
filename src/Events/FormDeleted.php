<?php

namespace Statamic\Events;

class FormDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Form deleted');
    }
}
