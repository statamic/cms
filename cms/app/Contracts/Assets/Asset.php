<?php

namespace Statamic\Contracts\Assets;

use Statamic\Contracts\Data\Data;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface Asset extends Data
{
    /**
     * Get the filename
     *
     * @return string
     */
    public function filename();

    /**
     * Get the basename
     *
     * @return string
     */
    public function basename();

    /**
     * Get or set the container
     *
     * @param null|string $id  ID of the container, if setting.
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function container($id = null);

    /**
     * Get or set the container by ID
     *
     * @param null|string $id  ID of the container, if setting.
     * @return string
     */
    public function containerId($id = null);

    /**
     * Get the URI
     *
     * @return string
     */
    public function uri();

    /**
     * Get the URL
     *
     * @return string
     */
    public function url();

    /**
     * Get the asset's absolute URL
     *
     * @return string
     * @throws \RuntimeException
     */
    public function absoluteUrl();

    /**
     * Get either a image URL builder instance, or a URL if passed params.
     *
     * @param null|array $params Optional manipulation parameters to return a string right away
     * @return \Statamic\Contracts\Imaging\UrlBuilder|string
     */
    public function manipulate($params = null);

    /**
     * Is this asset an image?
     *
     * @return bool
     */
    public function isImage();

    /**
     * Get the file extension
     *
     * @return string
     */
    public function extension();

    /**
     * Get the last modified date
     *
     * @return \Carbon\Carbon
     */
    public function lastModified();

    /**
     * Upload a file
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return mixed
     */
    public function upload(UploadedFile $file);
}
