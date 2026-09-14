<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\Video\Providers;
use Statamic\Fieldtypes\Video\Video as VideoDetails;

use function Statamic\trans as __;

class Video extends Fieldtype
{
    protected $categories = ['media'];

    public function augment($value)
    {
        if (is_null($value)) {
            return null;
        }

        return VideoDetails::fromValue($value);
    }

    public function preload()
    {
        $meta = [
            'providers' => Providers::options(),
            'url' => cp_route('video.details'),
        ];

        if (! is_null($value = $this->field()->value())) {
            $meta['video'] = VideoDetails::fromValue($value)->toArray();
        }

        return $meta;
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
