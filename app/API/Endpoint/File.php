<?php

namespace Statamic\API\Endpoint;

use Illuminate\Support\Facades\Storage;
use Statamic\Filesystem\FlysystemAdapter;
use Statamic\Filesystem\FilesystemAdapter;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Manipulating files on the local filesystem
 *
 * @method static \Illuminate\Contracts\Filesystem\Filesystem filesystem()
 * @method static string get(string $file, string $fallback = null)
 * @method static bool exists(string $file)
 * @method static bool put(string $file, string $contents)
 * @method static bool copy(string $src, string $dest, bool $overwrite = false)
 * @method static bool delete(string $file)
 * @method static bool rename(string $old, string $new)
 * @method static string extension(string $path)
 * @method static string mimeType(string $path)
 * @method static int lastModified(string $file)
 * @method static int size(string $file)
 * @method static string sizeHuman(string $file)
 * @method static bool isImage(string $file)
 */
class File
{
    /**
     * Get a filesystem disk.
     *
     * @param string|null $name  Either the name of a native filesystem (eg. "content"), null for the
     *                           project root, or the name of a user defined disk in filesystems.php
     * @return \Statamic\Filesystem\Filesystem
     */
    public function disk($name = null)
    {
        if ($name === null) {
            $name = 'standard';
        }

        try {
            $root = app("filesystems.paths.$name");
        } catch (\ReflectionException | BindingResolutionException $e) {
            return new FlysystemAdapter(Storage::disk($name));
        }

        $fs = app(FilesystemAdapter::class);
        $fs->setRootDirectory($root);
        return $fs;
    }

    /**
     * Pass methods through to the default disk
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->disk(), $method], $args);
    }
}
