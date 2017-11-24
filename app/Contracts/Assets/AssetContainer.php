<?php

namespace Statamic\Contracts\Assets;

use Statamic\Contracts\CP\Editable;

interface AssetContainer extends Editable
{
    /**
     * Get or set the ID
     *
     * @param null|string $id
     * @return string
     */
    public function id($id = null);

    /**
     * Get or set the handle
     *
     * @param null|string $handle
     * @return string
     */
    public function handle($handle = null);

    /**
     * Get or set the driver
     *
     * @param  null|string $driver
     * @return string
     */
    public function driver($driver = null);

    /**
     * Get or set the title
     *
     * @param null|string $title
     * @return string
     */
    public function title($title = null);

    /**
     * Get or set the data
     *
     * @param null|array $data
     * @return array|null
     */
    public function data($data = null);

    /**
     * Get or set the path
     *
     * @param null|string $path
     * @return string
     */
    public function path($path = null);

    /**
     * Get the full resolved path
     *
     * @return string
     */
    public function resolvedPath();

    /**
     * Get or set the URL to this location
     *
     * @param string|null $url
     * @return null|string
     */
    public function url($url = null);

    /**
     * Create an asset
     *
     * @param string $path
     * @return \Statamic\Assets\Asset
     */
    public function asset($path);

    /**
     * Get all the assets in this container
     *
     * @param string|null $folder Narrow down assets by folder
     * @param bool $recursive Whether to look for assets recursively
     * @return \Statamic\Assets\AssetCollection
     */
    public function assets($folder = null, $recursive = false);

    /**
     * Get all the asset files in this container
     *
     * @param string|null $folder  Narrow down assets by folder
     * @return \Illuminate\Support\Collection
     */
    public function files($folder = null);

    /**
     * Get all the subfolders in this container
     *
     * @param string|null $folder Narrow down subfolders by folder
     * @param bool $recursive
     * @return \Illuminate\Support\Collection
     */
    public function folders($folder = null, $recursive = false);

    /**
     * Save the container
     *
     * @return mixed
     */
    public function save();

    /**
     * Delete the container
     *
     * @return mixed
     */
    public function delete();

    /**
     * Get or set the fieldset to be used by assets in this container
     *
     * @param string $fieldset
     */
    public function fieldset($fieldset = null);

    /**
     * Whether the container's assets are web-accessible
     *
     * @return bool
     */
    public function accessible();
}
