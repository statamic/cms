<?php

namespace Statamic\Filesystem;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\FilesystemAdapter as IlluminateFilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class Manager
{
    /**
     * Get a filesystem disk.
     *
     * @param  string|null  $name  Either the name of a native filesystem (eg. "content"), null for the
     *                             project root, or the name of a user defined disk in filesystems.php
     * @return \Statamic\Filesystem\Filesystem
     */
    public function disk($name = null)
    {
        if ($name instanceof IlluminateFilesystemAdapter) {
            return new FlysystemAdapter($name);
        }

        if ($name === null) {
            $name = 'standard';
        }

        try {
            $root = app("filesystems.paths.$name");
        } catch (\ReflectionException|BindingResolutionException $e) {
            return new FlysystemAdapter(Storage::disk($name));
        }

        $fs = app(FilesystemAdapter::class);
        $fs->setRootDirectory($root);

        return $fs;
    }

    /**
     * Pass methods through to the default disk.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->disk(), $method], $args);
    }
}
