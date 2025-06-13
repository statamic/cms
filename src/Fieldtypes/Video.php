<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Video extends Fieldtype
{
    protected $categories = ['media'];

    public function preload()
    {
        /** @todo Fetch these from some repository so folks can add their own */
        return [
            ['name' => 'Cloudflare Stream', 'handle' => 'cloudflare_stream'],
            ['name' => 'Vimeo', 'handle' => 'vimeo'],
            ['name' => 'YouTube', 'handle' => 'youtube'],
        ];
    }

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                    ],
                    'placeholder' => [
                        'display' => __('Placeholder'),
                        'instructions' => __('statamic::fieldtypes.text.config.placeholder'),
                        'type' => 'text',
                    ],
                ],
            ],
        ];
    }
}
