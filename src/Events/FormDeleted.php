<?php

namespace Statamic\Events;

class FormDeleted extends Deleted
{
    public $form;

    public function __construct($form)
    {
        $this->form = $form;
    }

    public function commitMessage()
    {
        return __('Form deleted');
    }
}
