<?php

namespace Statamic\Support;

use Statamic\Assets\Asset;

class FileTypes
{
    public static function media(): array
    {
        return [
            ...self::image(),
            ...self::video(),
            ...self::audio(),
        ];
    }

    public static function image(): array
    {
        return [
            ...self::rasterImage(),
            ...self::vectorImage(),
        ];
    }

    public static function rasterImage(): array
    {
        return Asset::IMAGE_EXTENSIONS;
    }

    public static function vectorImage(): array
    {
        return ['svg'];
    }

    public static function video(): array
    {
        return Asset::VIDEO_EXTENSIONS;
    }

    public static function audio(): array
    {
        return Asset::AUDIO_EXTENSIONS;
    }

    public static function document(): array
    {
        return ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
    }

    public static function archive(): array
    {
        return ['zip', 'rar', 'gz'];
    }
}
