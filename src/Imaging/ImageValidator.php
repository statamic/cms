<?php

namespace Statamic\Imaging;

use Intervention\Image\Interfaces\DriverInterface;
use Symfony\Component\Mime\MimeTypes;

class ImageValidator
{
    public function __construct(private DriverInterface $driver)
    {
    }

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
        if (! $extension) {
            return false;
        }

        return $this->driver->supports($extension);
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
