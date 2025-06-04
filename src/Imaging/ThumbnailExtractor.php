<?php

namespace Statamic\Imaging;

use Statamic\Console\Processes\Ffmpeg;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Path;

class ThumbnailExtractor
{
    private Ffmpeg $ffmpeg;

    public function __construct(Ffmpeg $ffmpeg)
    {
        $this->ffmpeg = $ffmpeg;
    }

    public static function enabled()
    {
        return config(
            'statamic.assets.video_thumbnails',
            true
        );
    }

    public static function cachePath()
    {
        return config(
            'statamic.assets.ffmpeg.cache_path',
            storage_path('statamic/glide/ffmpeg')
        );
    }

    public static function hasCachedThumbnail(Asset $asset)
    {
        return file_exists(static::getCachePath($asset));
    }

    public static function getCachePath(Asset $asset)
    {
        $fileName = 'thumb_'.md5($asset->id()).'.jpg';
        $cacheDirectory = static::cachePath();
        $finalPath = Path::tidy($cacheDirectory.'/'.$fileName);

        if (! file_exists($cacheDirectory)) {
            mkdir($cacheDirectory, 0755, true);
        }

        return $finalPath;
    }

    public function generateThumbnail(Asset $asset)
    {
        $cachePath = static::getCachePath($asset);

        if (file_exists($cachePath)) {
            return $cachePath;
        }

        $ffmpegInput = null;

        if (file_exists($asset->resolvedPath())) {
            $ffmpegInput = $asset->resolvedPath();
        } elseif ($asset->container()->accessible()) {
            $ffmpegInput = $asset->absoluteUrl();
        } else {
            return null;
        }

        return $this->ffmpeg->extractThumbnail(
            $asset->absoluteUrl(),
            static::getCachePath($asset)
        );
    }
}
