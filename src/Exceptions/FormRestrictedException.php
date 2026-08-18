<?php

namespace Statamic\Exceptions;

use Statamic\Contracts\Forms\Form;

class FormRestrictedException extends \Exception
{
    public function __construct(protected Form $form)
    {
        parent::__construct($form->restrictionMessage());
    }

    public function form(): Form
    {
        return $this->form;
    }
}
