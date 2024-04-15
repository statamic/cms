<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Spacer extends Fieldtype
{
    protected $categories = ['special'];
    protected $selectableInForms = true;
    protected $icon = 'width';
}
