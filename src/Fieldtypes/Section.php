<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

/** @deprecated */
class Section extends Fieldtype
{
    protected $categories = ['special'];
    protected $localizable = false;
    protected $validatable = false;
    protected $defaultable = false;
    protected $selectable = false;
}
