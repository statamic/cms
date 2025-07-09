<?php

namespace Statamic\Fieldtypes;

use Embera\ProviderCollection\SlimProviderCollection;
use Statamic\Fields\Fieldtype;

class Video extends Fieldtype
{
    protected $categories = ['media'];

    public function augment($value)
    {
        /** @todo This is dumb */
        if (str($value)->isUrl()) {
            return $value;
        }

        //otherwise assume it's a Cloudflare ID
        return str($value)->afterLast('::')->value();
    }

    public function preload()
    {
        $providers = new Providers();

        /** @todo Fetch these from some repository so folks can add their own */
        return [
            'providers' => $providers->get(),
            'url' => cp_route('video.details'),
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

class Providers extends SlimProviderCollection
{
    public function get(): array
    {
        return collect($this->providers)
            ->unique()
            ->values()
            ->map(fn (string $class) => ['provider' => class_basename($class)])
            ->add(['provider' => 'Cloudflare'])
            ->sortBy('provider')
            ->values()
            ->all();
    }
}
