<?php

namespace Statamic\Validation;

use Statamic\Facades\Form;

class UniqueFormHandle
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        return ! Form::find($value);
    }
}
