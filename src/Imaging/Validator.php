<?php

namespace Statamic\Imaging;

use Statamic\Support\Str;
use Symfony\Component\Mime\MimeTypes;

class Validator
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
        if ($mimeType === null) {
            return false;
        }

        if (! $this->isAllowedExtension($extension)) {
            return false;
        }

        if (! $this->isAllowedMimeType($extension, $mimeType)) {
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
    protected function isAllowedExtension($extension)
    {
        $driver = config('statamic.assets.image_manipulation.driver');

        if ($driver == 'gd') {
            $allowed = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
        } elseif ($driver == 'imagick') {
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
    protected function isAllowedMimeType($extension, $mimeType)
    {
        if ($mimeType === null) {
            return false;
        }

        $allowedMimetypesForExtension = (new MimeTypes)->getMimeTypes($extension);

        ray($extension, $mimeType, $allowedMimetypesForExtension)->orange();

        return in_array($mimeType, $allowedMimetypesForExtension);
    }
}
