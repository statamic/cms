<?php

namespace Statamic\Filesystem;

use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemAdapter;

class FlysystemAdapter extends AbstractAdapter
{
    protected $filesystem;

    public function __construct(FilesystemAdapter $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    protected function normalizePath($path)
    {
        if ($path === '/' || $path === '.') {
            return null;
        }

        return $path;
    }

    public function getFiles($path, $recursive = false)
    {
        if (! $this->exists($path)) {
            return [];
        }

        return $this->filesystem->files($this->normalizePath($path), $recursive);
    }

    public function getFolders($path, $recursive = false)
    {
        $method = $recursive ? 'allDirectories' : 'directories';

        return $this->filesystem->$method($this->normalizePath($path));
    }

    public function copyDirectory($src, $dest, $overwrite = false)
    {
        $src = $this->normalizePath($src);
        $dest = $this->normalizePath($dest);

        foreach ($this->getFilesRecursively($src) as $old) {
            $new = preg_replace('#^'.$src.'#', $dest, $old);
            $this->copy($old, $new, $overwrite);
        }
    }

    public function moveDirectory($src, $dest, $overwrite = false)
    {
        $src = $this->normalizePath($src);
        $dest = $this->normalizePath($dest);

        foreach ($this->getFilesRecursively($src) as $old) {
            $new = preg_replace('#^'.$src.'#', $dest, $old);
            $this->move($old, $new, $overwrite);
        }

        $this->delete($src);
    }

    public function url($path)
    {
        return $this->filesystem->url($path);
    }
}