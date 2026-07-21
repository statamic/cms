<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Paragraph extends FormFieldtype
{
    protected static $fieldtype = 'html';
    protected $description = 'A paragraph to provide information in your form.';
    protected $icon = 'text-short';
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
            'instructions' => ['type' => 'hidden'],
            'text' => [
                'display' => __('Text'),
                'type' => 'textarea',
                'validate' => 'required',
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'form_paragraph',
            'text' => $this->config('text'),
            'display' => $this->config('display'),
            'hide_display' => true,
            'listable' => false,
            ...Arr::except($this->config(), ['type', 'text', 'display', 'listable']),
        ];
    }
}
