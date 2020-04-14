<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Query\Scopes\Filters\Fields\Textarea as TextareaFilter;

class Textarea extends Fieldtype
{
    protected function configFieldItems(): array
    {
        return [
            'character_limit' => [
                'display' => __('Character Limit'),
                'instructions' => __('statamic::fieldtypes.text.config.character_limit'),
                'type' => 'text',
            ],
        ];
    }

    protected $view = 'statamic::forms.fields.textarea';

    public function filter()
    {
        return new TextareaFilter($this);
    }
}
