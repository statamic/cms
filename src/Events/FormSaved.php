<?php

namespace Statamic\Events;

class FormSaved extends Saved
{
    public function commitMessage()
    {
        return __('Form saved');
    }
}
