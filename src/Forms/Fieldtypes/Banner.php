<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Fieldtypes\Info;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Banner extends FormFieldtype
{
    protected static $fieldtype = 'info';
    protected $description = 'A banner to highlight important information in your form.';
    protected $icon = 'banner';
    protected $categories = ['information'];

    protected function configFieldItems(): array
    {
        return [
            'display' => [
                'display' => __('Label'),
                'instructions' => __('statamic::form-fieldtypes.paragraph.config.display.instructions'),
                'type' => 'text',
                'focus' => true,
                'validate' => 'required',
            ],
            'handle' => FormField::commonFieldOptions()->get('handle')->config(),
            'instructions' => ['type' => 'hidden'],
            ...app(Info::class)->configFields()->all()->map->config()->all(),
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'info',
            'hide_display' => true,
            'listable' => false,
            ...Arr::except($this->config(), ['type', 'listable']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'Important',
                'content' => 'Please review the following information before continuing.',
                'state' => 'tip',
            ],
        ];
    }
}
