<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Group extends FormFieldtype
{
    protected static $fieldtype = 'group';
    protected $description = 'Group related fields together.';
    protected $icon = 'fieldtype-group';
    protected $categories = ['structure'];

    protected function configFieldItems(): array
    {
        return [
            'fields' => [
                'display' => __('Fields'),
                'instructions' => __('statamic::fieldtypes.group.config.fields'),
                'type' => 'fields',
                'full_width_setting' => true,
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'group',
            'fields' => $this->config('fields', []),
            ...Arr::except($this->config(), ['type', 'fields']),
        ];
    }
}
