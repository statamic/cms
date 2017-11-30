<?php

namespace Statamic\API;

use Statamic\Filesystem\FileAccessor;
use Statamic\Filesystem\FilesystemAdapter;

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
     * Get a disk
     *
     * @param string|null $name
     * @return \Statamic\Filesystem\FileAccessor
     */
    public static function disk($name = null)
    {
        $disk = app(FilesystemAdapter::class);

        if ($name === null) {
            //
        } elseif ($name === 'content') {
            $disk->setRootDirectory(base_path('content'));
        } elseif ($name === 'theme') {
            $disk->setRootDirectory(base_path('resources'));
        } elseif ($name === 'users') {
            $disk->setRootDirectory(base_path('users'));
        } elseif ($name === 'storage') {
            $disk->setRootDirectory(storage_path('statamic'));
        } else {
            throw new \Exception('Todo: Handle [' . $name . '] disk.');
        }

        return $disk;
    }

    /**
     * Pass methods through to the default disk
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return call_user_func_array([self::disk(), $method], $args);
    }
}
