<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Html extends Fieldtype
{
    protected static $title = 'HTML';
    protected $icon = 'html';

    protected $configFields = [
        'html' => [
            'display' => 'HTML',
            'type' => 'code',
            'mode' => 'htmlmixed',
        ],
    ];

    protected function defaultConfigFieldItem(): ?array
    {
        return array_replace(parent::defaultConfigFieldItem(), ['type' => 'html']);
    }
}
