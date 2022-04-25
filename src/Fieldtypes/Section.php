<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Section extends Fieldtype
{
    protected $categories = ['special'];
    protected $localizable = false;
    protected $validatable = false;
    protected $defaultable = false;
}
