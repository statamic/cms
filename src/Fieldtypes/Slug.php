<?php

namespace Statamic\Fieldtypes;

use Faker\Generator;
use Illuminate\Support\Str as IlluminateStr;

use function Statamic\trans as __;

class Slug extends Text
{
    protected $categories = ['special'];

    protected $selectableInForms = false;

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Input Behavior'),
                'fields' => [
                    'generate' => [
                        'display' => __('Generate'),
                        'type' => 'toggle',
                        'instructions' => __('statamic::fieldtypes.slug.config.generate'),
                        'default' => true,
                        'width' => 50,
                    ],
                    'from' => [
                        'display' => __('From'),
                        'type' => 'text',
                        'instructions' => __('statamic::fieldtypes.slug.config.from'),
                        'default' => 'title',
                        'width' => 50,
                    ],
                    'show_regenerate' => [
                        'display' => __('Show Regenerate Button'),
                        'type' => 'toggle',
                        'instructions' => __('statamic::fieldtypes.slug.config.show_regenerate'),
                        'default' => false,
                        'width' => 50,
                    ],
                ],
            ],
        ];
    }

    public function previewValue(): mixed
    {
        return IlluminateStr::slug(__($this->field()->display()));
    }

    public function fakeValue(Generator $faker): mixed
    {
        return IlluminateStr::slug(parent::fakeValue($faker));
    }
}
