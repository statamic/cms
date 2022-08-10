<?php

namespace Statamic\Contracts\Assets;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface Asset
{
    /**
     * Get the filename.
     *
     * @return string
     */
    public function filename();

    /**
     * Get the basename.
     *
     * @return string
     */
    public function basename();

    /**
     * Get or set the container.
     *
     * @param  AssetContainer|string  $container  An asset container instance, or the handle of one.
     * @return AssetContainer
     */
    public function container($container = null);

    /**
     * Get the URL.
     *
     * @return string
     */
    public function url();

    /**
     * Get either a image URL builder instance, or a URL if passed params.
     *
     * @param  null|array  $params  Optional manipulation parameters to return a string right away
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
     * Get the file extension.
     *
     * @return string
     */
    public function extension();

    /**
     * Get the last modified date.
     *
     * @return \Carbon\Carbon
     */
    public function lastModified();

    /**
     * Upload a file.
     *
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile  $file
     * @return mixed
     */
    public function upload(UploadedFile $file);

    /**
     * Download a file.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(string $name = null, array $headers = []);

    /**
     * Get the asset file contents.
     *
     * @return mixed
     */
    public function contents();
}
