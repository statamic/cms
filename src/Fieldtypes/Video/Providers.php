<?php

namespace Statamic\Fieldtypes\Video;

use Embera\ProviderCollection\ProviderCollectionAdapter;

class Providers extends ProviderCollectionAdapter
{
    const CLOUDFLARE = 'cloudflare';
    const FILE = 'file';
    const UNSUPPORTED = 'unsupported';

    protected static array $oembed = [
        'Bunny',
        'Coub',
        'DailyMotion',
        'Loom',
        'Rumble',
        'SproutVideo',
        'Streamable',
        'Ted',
        'TikTok',
        'Vidyard',
        'Vimeo',
        'Wistia',
        'Youtube',
    ];

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->registerProvider(static::$oembed);
    }

    public static function options(): array
    {
        return collect(static::$oembed)
            ->map(fn (string $provider) => ['value' => $provider, 'label' => $provider])
            ->push(['value' => self::CLOUDFLARE, 'label' => __('Cloudflare Stream')])
            ->push(['value' => self::FILE, 'label' => __('Video File')])
            ->sortBy('label')
            ->values()
            ->all();
    }
}
