<?php

namespace Statamic\Contracts\Assets;

interface AssetContainer
{
    /**
     * Get or set the ID.
     *
     * @param  null|string  $id
     * @return string
     */
    public function id($id = null);

    /**
     * Get or set the handle.
     *
     * @param  null|string  $handle
     * @return string
     */
    public function handle($handle = null);

    /**
     * Get or set the title.
     *
     * @param  null|string  $title
     * @return string
     */
    public function title($title = null);

    /**
     * Get the URL to this location.
     *
     * @return null|string
     */
    public function url();

    /**
     * Create an asset.
     *
     * @param  string  $path
     * @return \Statamic\Assets\Asset
     */
    public function asset($path);

    /**
     * Get all the assets in this container.
     *
     * @param  string|null  $folder  Narrow down assets by folder
     * @param  bool  $recursive  Whether to look for assets recursively
     * @return \Statamic\Assets\AssetCollection
     */
    public function assets($folder = null, $recursive = false);

    /**
     * Get all the asset files in this container.
     *
     * @param  string|null  $folder  Narrow down assets by folder
     * @return \Illuminate\Support\Collection
     */
    public function files($folder = null);

    /**
     * Get all the subfolders in this container.
     *
     * @param  string|null  $folder  Narrow down subfolders by folder
     * @param  bool  $recursive
     * @return \Illuminate\Support\Collection
     */
    public function folders($folder = null, $recursive = false);

    /**
     * Save the container.
     *
     * @return mixed
     */
    public function save();

    /**
     * Delete the container.
     *
     * @return mixed
     */
    public function delete();

    /**
     * Get the blueprint to be used by assets in this container.
     */
    public function blueprint();

    /**
     * Whether the container's assets are web-accessible.
     *
     * @return bool
     */
    public function accessible();

    /**
     * Whether the container's assets are not web-accessible.
     *
     * @return bool
     */
    public function private();
}
