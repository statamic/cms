<?php

namespace Statamic\Fieldtypes\Markdown;

use Statamic\Fields\Fieldtype;

class Buttons extends Fieldtype
{
    public static $handle = 'markdown_buttons_setting';
    protected $selectable = false;
}
