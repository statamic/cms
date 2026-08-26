<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class LongAnswer extends FormFieldtype
{
    protected static $fieldtype = 'textarea';
    protected $description = 'A larger field for detailed responses, comments, or messages.';
    protected $icon = 'text-long';
    protected $categories = ['text'];
    protected $keywords = ['textarea'];
    protected $order = 2;

    public function configFieldItems(): array
    {
        return [
            'placeholder' => [
                'display' => __('Placeholder'),
                'instructions' => __('statamic::fieldtypes.text.config.placeholder'),
                'type' => 'text',
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'textarea',
            'placeholder' => $this->config('placeholder'),
            ...Arr::except($this->config(), ['type', 'placeholder']),
        ];
    }

    public function defaultChart(): ?string
    {
        return HorizontalBar::class;
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'Tell us about your dream vacation',
            ],
            'value' => 'A cozy cabin in the mountains with a stack of books, endless coffee, and absolutely no wifi.',
        ];
    }
}
