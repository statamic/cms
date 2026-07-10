<?php

namespace Statamic\CP\Assets;

use Intervention\Image\ImageManager;

/**
 * @internal
 */
class CropProcessor
{
    public function crop(string $contents, string $extension, int $x, int $y, int $width, int $height, ?int $quality = null, ?string $background = null): string
    {
        // Bake in EXIF orientation so the crop coordinates, which come from the
        // auto-oriented image the user saw in the browser, line up with the source.
        $image = $this->manager()->read($contents)->orient();

        $x = max(0, min($x, $image->width() - 1));
        $y = max(0, min($y, $image->height() - 1));
        $width = min($width, $image->width() - $x);
        $height = min($height, $image->height() - $y);

        $image->crop($width, $height, $x, $y);

        // JPEG has no alpha channel, so flatten any transparency onto a
        // background colour rather than letting it default to black.
        if (in_array(strtolower($extension), ['jpg', 'jpeg'])) {
            $image->blendTransparency($background ?? 'ffffff');
        }

        return (string) $image->encodeByExtension($extension, quality: $quality ?? self::defaultQuality());
    }

    public static function defaultQuality(): int
    {
        return config('statamic.assets.image_manipulation.crop_quality')
            ?? config('statamic.assets.image_manipulation.defaults.quality')
            ?? 90;
    }

    protected function manager(): ImageManager
    {
        $driver = config('statamic.assets.image_manipulation.driver', 'gd');

        return match ($driver) {
            'gd' => ImageManager::gd(),
            'imagick' => ImageManager::imagick(),
            default => ImageManager::withDriver($driver),
        };
    }
}
