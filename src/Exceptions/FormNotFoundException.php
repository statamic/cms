<?php

namespace Statamic\Exceptions;

class FormNotFoundException extends \Exception
{
    protected $form;

    public function __construct($form)
    {
        parent::__construct("Form [{$form}] not found");

        $this->form = $form;
    }
}
