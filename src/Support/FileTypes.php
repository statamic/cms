<?php

namespace Statamic\Support;

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
        return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
    }

    public static function vectorImage(): array
    {
        return ['svg'];
    }

    public static function video(): array
    {
        return ['h264', 'mp4', 'm4v', 'ogv', 'webm', 'mov'];
    }

    public static function audio(): array
    {
        return ['aac', 'flac', 'm4a', 'mp3', 'ogg', 'wav'];
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
