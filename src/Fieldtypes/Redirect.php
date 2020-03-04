<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Routing\ResolveRedirect;

class Redirect extends Fieldtype
{
    public function augment($value)
    {
        return (new ResolveRedirect)($value, $this->field->parent());
    }
}
