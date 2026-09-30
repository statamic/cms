<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\Video\Embed;

use function Statamic\trans as __;

class Video extends Fieldtype
{
    protected $categories = ['media'];

    public function augment($value)
    {
        if (is_null($value)) {
            return null;
        }

        return Embed::fromValue($value);
    }

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance'),
                'fields' => [
                    'placeholder' => [
                        'display' => __('Placeholder'),
                        'instructions' => __('statamic::fieldtypes.text.config.placeholder'),
                        'type' => 'text',
                    ],
                ],
            ],
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
}
