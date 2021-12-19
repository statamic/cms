<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Html extends Fieldtype
{
    protected $categories = ['text'];
    protected $icon = 'html';

    protected function configFieldItems(): array
    {
        return [
            'html' => [
                'display' => 'HTML',
                'type' => 'code',
                'mode' => 'htmlmixed',
            ],
        ];
    }
}
