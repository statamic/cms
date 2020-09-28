<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Html extends Fieldtype
{
    protected static $title = 'HTML';
    protected $icon = 'code';

    protected $configFields = [
        'html' => [
            'display' => 'HTML',
            'type' => 'code',
            'mode' => 'htmlmixed',
        ],
    ];
}
