<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Text extends Fieldtype
{
    protected $configFields = [
        'placeholder' => ['type' => 'text'],
        'character_limit' => ['type' => 'integer'],
        'prepend' => ['type' => 'text'],
        'append' => ['type' => 'text'],
    ];
}