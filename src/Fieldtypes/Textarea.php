<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Query\Scopes\Filters\Fields\Textarea as TextareaFilter;

class Textarea extends Fieldtype
{
    protected $configFields = [
        'character_limit' => [
            'type' => 'text',
            'instructions' => 'Set the maximum number of enterable characters.'
        ],
    ];

    protected $view = 'statamic::forms.fields.textarea';

    public function filter()
    {
        return new TextareaFilter($this);
    }
}
