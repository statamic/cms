<?php

namespace Statamic\Exceptions;

use Statamic\Contracts\Forms\Form;
use Statamic\Forms\Instance;

class FormRestrictedException extends \Exception
{
    public function __construct(protected Instance $instance)
    {
        parent::__construct($instance->restrictionMessage());
    }

    public function form(): Form
    {
        return $this->instance->form();
    }
}
