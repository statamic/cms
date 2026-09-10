<?php

namespace Statamic\Fieldtypes\Video;

use Embera\ProviderCollection\SlimProviderCollection;

class Providers extends SlimProviderCollection
{
    public static function get(): array
    {
        return collect((new self)->providers)
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
