<?php

namespace Statamic\API;

use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

class Zip
{
    /**
     * Create a zip from a path
     *
     * @param string $path
     * @return \League\Flysystem\Filesystem
     */
    private static function zip($path)
    {
        $path = root_path(Path::makeRelative($path));
        $path = Str::ensureRight($path, '.zip');

        return new Filesystem(new ZipArchiveAdapter($path));
    }

    /**
     * Make a zip file
     *
     * Technically no difference between make and get.
     *
     * @param string $path
     * @return \League\Flysystem\Filesystem
     */
    public static function make($path)
    {
        return self::zip($path);
    }

    /**
     * Get a zip file
     *
     * Technically no difference between make and get.
     *
     * @param string $path
     * @return \League\Flysystem\Filesystem
     */
    public static function get($path)
    {
        return self::zip($path);
    }

    /**
     * Extract a zip to a folder
     *
     * @param string $path        Path to zip
     * @param string $destination Where it should be extracted
     */
    public static function extract($path, $destination)
    {
        $zip = self::zip($path);

        $zip->getAdapter()->getArchive()->extractTo($destination);
    }

    /**
     * Write a zip to file
     *
     * @param Filesystem $zip
     */
    public static function write($zip)
    {
        $zip->getAdapter()->getArchive()->close();
    }
}
