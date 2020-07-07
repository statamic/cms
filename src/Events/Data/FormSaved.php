<?php

namespace Statamic\Events\Data;

class FormSaved extends Saved
{
    public function commitMessage()
    {
        return __('Form saved');
    }
}
