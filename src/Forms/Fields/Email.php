<?php

namespace Statamic\Forms\Fields;

use Statamic\Support\Arr;

class Email extends FormField
{
    protected static $handle = 'email';
    protected $icon = 'mail-sign-at';
    protected $categories = ['Contact Info'];
    protected $keywords = ['mailto', 'website'];

    public function configFieldItems(): array
    {
        return [
//            'placeholder' => ['display' => 'Placeholder'],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'text',
            'validate' => [...$this->config('validate'), 'email'],
            'input_type' => 'email',
//            'placeholder' => $this->config('placeholder'),
            ...Arr::except($this->config(), ['input_type', 'validate']),
        ];
    }
}
