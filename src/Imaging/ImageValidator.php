<?php

namespace Statamic\Imaging;

use Statamic\Support\Str;
use Symfony\Component\Mime\MimeTypes;

class ImageValidator
{
    /**
     * Check if image has valid extension and mimetype.
     *
     * @param  string  $extension
     * @param  string  $mimeType
     * @return bool
     */
    public function isValidImage($extension, $mimeType)
    {
        if (! $this->isValidExtension($extension)) {
            return false;
        }

        if (! $this->isValidMimeType($extension, $mimeType)) {
            return false;
        }

        return true;
    }

    /**
     * Check if an extension is allowed by the configured image manipulation driver.
     *
     * @see https://image.intervention.io/v2/introduction/formats
     *
     * @param  string  $extension
     * @return bool
     *
     * @throws \Exception
     */
    public function isValidExtension($extension)
    {
        $driver = config('statamic.assets.image_manipulation.driver');

        if ($driver == 'gd') {
            $allowed = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
        } elseif ($driver == 'imagick') {
            $allowed = ['jpeg', 'jpg', 'png', 'gif', 'tif', 'bmp', 'psd', 'webp'];
        } elseif ($driver == \Intervention\Image\Drivers\Vips\Driver::class) {
            $allowed = ['jpeg', 'jpg', 'png', 'gif', 'tif', 'bmp', 'psd', 'webp'];
        } else {
            throw new \Exception("Unsupported image manipulation driver [$driver]");
        }

        $additional = config('statamic.assets.image_manipulation.additional_extensions', []);

        return collect($allowed)
            ->merge($additional)
            ->map(fn ($extension) => Str::lower($extension))
            ->contains(Str::lower($extension));
    }

    /**
     * Check if mimetype is allowed for specific extension.
     *
     * @param  string  $extension
     * @param  string  $mimeType
     * @return bool
     */
    private function isValidMimeType($extension, $mimeType)
    {
        $allowedMimetypesForExtension = (new MimeTypes)->getMimeTypes($extension);

        return in_array($mimeType, $allowedMimetypesForExtension);
    }
}
