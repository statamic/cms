<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Query\Scopes\Filters\Fields\Textarea as TextareaFilter;

class Textarea extends Fieldtype
{
    protected function configFieldItems(): array
    {
        return [
            'placeholder' => [
                'display' => __('Placeholder'),
                'instructions' => __('statamic::fieldtypes.text.config.placeholder'),
                'type' => 'text',
                'width' => 50,
            ],
            'character_limit' => [
                'display' => __('Character Limit'),
                'instructions' => __('statamic::fieldtypes.text.config.character_limit'),
                'type' => 'text',
            ],
            'antlers' => [
                'display' => 'Antlers',
                'instructions' => __('statamic::fieldtypes.any.config.antlers'),
                'type' => 'toggle',
                'width' => 50,
            ],
        ];
    }

    public function filter()
    {
        return new TextareaFilter($this);
    }
}
