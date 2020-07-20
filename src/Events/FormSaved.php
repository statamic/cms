<?php

namespace Statamic\Events;

class FormSaved extends Saved
{
    public $form;

    public function __construct($form)
    {
        $this->form = $form;
    }

    public function commitMessage()
    {
        return __('Form saved');
    }
}
