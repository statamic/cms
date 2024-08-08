<?php

namespace Statamic\Assets\Thumbnails;

use Statamic\Contracts\Assets\Asset;

class ThumbnailService
{
    protected static $generators = [];

    public static function generate(Asset $asset, mixed $params = null): ?string
    {
        if ($generator = static::findGenerator($asset)) {
            return $generator->generate($asset, $params);
        }

        return null;
    }

    protected static function findGenerator(Asset $asset): ?ThumbnailGenerator
    {
        return app('statamic.thumbnail-generators')
            ->reverse()
            ->map(fn ($class) => static::makeGenerator($class))
            ->first(fn ($generator) => $generator->accepts($asset));
    }

    protected static function makeGenerator(string $class): ThumbnailGenerator
    {
        $instance = static::$generators[$class] ?? new $class();

        if (! $instance instanceof ThumbnailGenerator) {
            throw new \Exception("Thumbnail generator must extend [Statamic\Assets\Thumbnails\ThumbnailGenerator]!");
        }

        static::$generators[$class] = $instance;

        return $instance;
    }
}
