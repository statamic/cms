<?php

namespace Statamic\Fieldtypes\Video;

use Embera\ProviderCollection\ProviderCollectionAdapter;

class Providers extends ProviderCollectionAdapter
{
    protected static array $names = [
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

        $this->registerProvider(static::$names);
    }

    public static function names(): array
    {
        return static::$names;
    }
}
