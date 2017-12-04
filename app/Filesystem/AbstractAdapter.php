<?php

namespace Statamic\Filesystem;

use Statamic\API\Str;
use Statamic\API\Helper;
use Statamic\Filesystem\Filesystem;

abstract class AbstractAdapter implements Filesystem
{
    public function get($path, $fallback = null)
    {
        if (! $this->exists($path)) {
            return $fallback;
        }

        return $this->filesystem->get($this->normalizePath($path));
    }

    public function exists($path = null)
    {
        if ($path === '/' || $path === null) {
            return true;
        }

        return $this->filesystem->exists($this->normalizePath($path));
    }

    public function put($path, $contents)
    {
        $this->makeDirectory(pathinfo($path)['dirname']);

        $this->filesystem->put($this->normalizePath($path), $contents);
    }

    public function delete($path)
    {
        $path = $this->normalizePath($path);

        if ($this->isDirectory($path)) { // not under test
            return $this->filesystem->deleteDirectory($path);
        }

        return $this->filesystem->delete($path);
    }

    public function copy($src, $dest, $overwrite = false)
    {
        if ($this->isDirectory($src)) { // not under test
            return $this->copyDirectory($src, $dest, $overwrite);
        }

        if ($overwrite && $this->exists($dest)) {
            $this->delete($dest);
        }

        return $this->filesystem->copy($this->normalizePath($src), $this->normalizePath($dest));
    }

    public function move($src, $dest, $overwrite = false)
    {
        if ($this->isDirectory($src)) { // not under test
            return $this->moveDirectory($src, $dest, $overwrite);
        }

        if ($overwrite && $this->exists($dest)) {
            $this->delete($dest);
        }

        return $this->filesystem->move($this->normalizePath($src), $this->normalizePath($dest));
    }

    public function rename($old, $new)
    {
        return $this->move($old, $new);
    }

    public function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    public function mimeType($path)
    {
        return $this->filesystem->mimeType($this->normalizePath($path));
    }

    public function lastModified($path)
    {
        return $this->filesystem->lastModified($this->normalizePath($path));
    }

    public function size($file)
    {
        return $this->filesystem->size($this->normalizePath($file));
    }

    public function sizeHuman($file)
    {
        return Str::fileSizeForHumans($this->size($file), 2);
    }

    public function isImage($path)
    {
        return in_array(
            strtolower($this->extension($path)),
            ['jpg', 'jpeg', 'png', 'gif']
        );
    }

    public function makeDirectory($path)
    {
        return $this->filesystem->makeDirectory($this->normalizePath($path), 0755, true, true);
    }

    public function getFilesRecursively($path)
    {
        return $this->getFiles($path, true);
    }

    public function getFilesRecursivelyExcept($path, $exclude = [])
    {
        $files = collect($this->getFiles($path));
        $folders = $this->getFolders($path);

        foreach ($folders as $folder) {
            $fn = pathinfo($folder)['filename'];
            if (in_array($fn, $exclude)) {
                continue;
            }

            $files = $files->merge($this->getFilesRecursively($folder));
        }

        return $files->all();
    }

    public function getFoldersRecursively($folder)
    {
        return $this->getFolders($folder, true);
    }

    public function getFilesByType($folder, $extension, $recursive = false)
    {
        $extensions = Helper::ensureArray($extension);

        $files = $this->getFiles($folder, $recursive);

        return collect($files)->filter(function ($file) use ($extensions) {
            return in_array($this->extension($file), $extensions);
        })->all();
    }

    public function getFilesByTypeRecursively($folder, $extension)
    {
        return $this->getFilesByType($folder, $extension, true);
    }

    public function isEmpty($folder)
    {
        $files = $this->getFilesRecursively($folder);

        return empty($files);
    }

    public function isDirectory($path)
    {
        return $this->filesystem->exists($this->normalizePath($path))
            && !$this->extension($path);
    }

    public function deleteEmptySubfolders($path)
    {
        // Grab all the folders
        $folders = $this->getFoldersRecursively($path);

        // Sort by deepest first. In order to delete a folder, it must be empty.
        // This means we need to delete the deepest child folders first.
        uasort($folders, function ($a, $b) {
            return (substr_count($a, '/') >= substr_count($b, '/')) ? -1 : 1;
        });

        // Iterate and delete
        foreach ($folders as $dir) {
            if ($this->isEmpty($dir)) {
                $this->delete($dir);
            }
        }
    }

    public function filesystem()
    {
        return $this->filesystem;
    }

    protected function relativePath($path)
    {
        return Str::removeLeft($path, $this->root);
    }
}