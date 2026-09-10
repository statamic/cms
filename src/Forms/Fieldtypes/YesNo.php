<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class YesNo extends FormFieldtype
{
    protected static $title = 'Yes/No';
    protected static $fieldtype = 'radio';
    protected $description = 'A simple yes or no question.';
    protected $icon = 'checkmark-circle';
    protected $categories = ['choice'];
    protected $order = 2;

    public function configFieldItems(): array
    {
        return [];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'yes_no',
            'appearance' => 'chips',
            'options' => [
                'yes' => __('Yes'),
                'no' => __('No'),
            ],
            ...Arr::except($this->config(), ['type', 'options']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'Would you recommend us to a friend?',
            ],
            'value' => 'yes',
        ];
    }
}
