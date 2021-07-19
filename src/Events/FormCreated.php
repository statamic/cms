<?php

namespace Statamic\Events;

class FormCreated extends Event
{
    public $form;

    public function __construct($form)
    {
        $this->form = $form;
    }
}
