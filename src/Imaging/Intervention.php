<?php

namespace Statamic\Imaging;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ColorInterface;
use Intervention\Image\Interfaces\DriverInterface;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Smooths over the differences between Intervention Image v3 and v4.
 *
 * Glide 3 requires Intervention v3 and Glide 4 requires v4, and we support both. Most of the
 * API is identical across the two, but v4 renamed a handful of the methods we rely on.
 *
 * @internal
 */
class Intervention
{
    public static function isV4(): bool
    {
        // v4 dropped the driver-specific named constructors in favour of usingDriver().
        return ! method_exists(ImageManager::class, 'withDriver');
    }

    public static function driver(?string $driver = null): DriverInterface
    {
        $driver ??= config('statamic.assets.image_manipulation.driver', 'gd');

        $class = match ($driver) {
            'gd' => GdDriver::class,
            'imagick' => ImagickDriver::class,
            default => $driver,
        };

        return new $class;
    }

    public static function manager(?string $driver = null): ImageManager
    {
        return new ImageManager(static::driver($driver));
    }

    public static function decode(ImageManager $manager, string $contents): ImageInterface
    {
        return static::isV4()
            ? $manager->decodeBinary($contents)
            : $manager->read($contents);
    }

    public static function create(ImageManager $manager, int $width, int $height): ImageInterface
    {
        return static::isV4()
            ? $manager->createImage($width, $height)
            : $manager->create($width, $height);
    }

    public static function colorAt(ImageInterface $image, int $x, int $y): ColorInterface
    {
        return static::isV4()
            ? $image->colorAt($x, $y)
            : $image->pickColor($x, $y);
    }

    public static function fillTransparentAreas(ImageInterface $image, string $color): ImageInterface
    {
        return static::isV4()
            ? $image->fillTransparentAreas($color)
            : $image->blendTransparency($color);
    }

    public static function encodeByExtension(ImageInterface $image, string $extension, ?int $quality = null): EncodedImageInterface
    {
        // v4's encoders type the quality as an int, so leave it out entirely rather
        // than passing null through and letting the format's default apply.
        $options = $quality === null ? [] : ['quality' => $quality];

        return static::isV4()
            ? $image->encodeUsingFileExtension($extension, ...$options)
            : $image->encodeByExtension($extension, ...$options);
    }
}
