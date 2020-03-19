<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Textarea extends Fieldtype
{
    protected $configFields = [
        'character_limit' => [
            'type' => 'text',
            'instructions' => 'Set the maximum number of enterable characters.'
        ],
    ];

    protected $view = 'statamic::forms.fields.textarea';
}
