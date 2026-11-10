<?php

namespace Statamic\Fieldtypes;

use Faker\Generator;
use Statamic\Fields\Fieldtype;
use Statamic\Forms\PreviewSubmission;

use function Statamic\trans as __;

class Hidden extends Fieldtype
{
    protected $categories = ['special'];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Data & Format'),
                'fields' => [
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                    ],
                ],
            ],
        ];
    }

    public function previewValue(): mixed
    {
        return PreviewSubmission::placeholder($this->field());
    }

    public function fakeValue(Generator $faker): mixed
    {
        return app(Text::class)->setField($this->field())->fakeValue($faker);
    }
}
