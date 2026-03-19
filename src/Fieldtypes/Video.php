<?php

namespace Statamic\Fieldtypes;

use Embera\ProviderCollection\SlimProviderCollection;
use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\Video\Video as VideoDetails;

class Video extends Fieldtype
{
    protected $categories = ['media'];

    public function augment($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (str($value)->isUrl()) {
            return $value;
        }

        //otherwise assume it's a Cloudflare ID
        return str($value)->afterLast('::')->value();
    }

    public function preload()
    {
        $meta = [
            'providers' => Providers::get(),
            'url' => cp_route('video.details'),
        ];

        if (! is_null($url = $this->field()->value())) {
            $video = VideoDetails::fromUrl($url);

            /** @todo Fetch these from some repository so folks can add their own */
            $meta['embed'] = $video->embed;
            $meta['provider'] = $video->provider;
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

class Providers extends SlimProviderCollection
{
    public static function get(): array
    {
        return collect((new static())->providers)
            ->unique()
            ->values()
            ->map(fn (string $class) => ['provider' => class_basename($class)])
            ->add(['provider' => 'Cloudflare'])
            ->sortBy('provider')
            ->add(['provider' => 'Not Supported'])
            ->values()
            ->all();
    }
}
