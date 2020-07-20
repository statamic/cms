<?php

namespace Statamic\Events;

class FormSubmitted extends Saving
{
    public $form;

    public function __construct($form)
    {
        $this->form = $form;
    }
}
