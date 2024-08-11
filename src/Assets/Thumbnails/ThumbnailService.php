<?php

namespace Statamic\Assets\Thumbnails;

use Illuminate\Support\Collection;
use Statamic\Contracts\Assets\Asset;

class ThumbnailService
{
    protected static $coreGenerators = [
        ImageThumbnailGenerator::class,
        SvgThumbnailGenerator::class,
    ];

    protected static $generatorInstances = [];

    public static function generate(Asset $asset, mixed $params = null): ?string
    {
        if ($generator = static::generator($asset)) {
            return $generator->generate($asset, $params);
        }

        return null;
    }

    public static function generators(): Collection
    {
        return collect(config('statamic.cp.thumbnail_generators', []))
            ->concat(static::$coreGenerators);
    }

    public static function generator(Asset $asset): ?ThumbnailGenerator
    {
        return static::generators()
            ->map(fn ($class) => static::makeGenerator($class))
            ->first(fn ($generator) => $generator->accepts($asset));
    }

    protected static function makeGenerator(string $class): ThumbnailGenerator
    {
        $instance = static::$generatorInstances[$class] ?? new $class();
        if (! $instance instanceof ThumbnailGenerator) {
            throw new \Exception("Thumbnail generator must extend [Statamic\Assets\Thumbnails\ThumbnailGenerator]!");
        }

        return static::$generatorInstances[$class] = $instance;
    }
}
