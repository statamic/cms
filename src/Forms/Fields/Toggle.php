<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

class Toggle extends FormFieldtype
{
    protected static $fieldtype = 'toggle';

    public function configFieldItems(): array
    {
        return [
            'inline_label' => [
                'display' => __('Inline Label'),
                'instructions' => __('statamic::fieldtypes.toggle.config.inline_label'),
                'type' => 'text',
                'default' => '',
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'toggle',
            'inline_label' => $this->config('inline_label'),
            ...Arr::except($this->config(), ['type', 'inline_label']),
        ];
    }
}
