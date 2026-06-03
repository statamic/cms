<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Facades\Markdown;
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
            'content' => [
                'display' => __('Content'),
                'instructions' => __('statamic::form-fields.paragraph.config.content'),
                'type' => 'textarea',
            ],
        ];
    }

    public function toFieldArray(): array
    {
        $content = $this->config('content');
        $html = $content ? Markdown::parse($content) : null;

        return [
            'type' => 'html',
            'html' => $html,
            'hide_display' => true,
            ...Arr::except($this->config(), ['type', 'content']),
        ];
    }
}
