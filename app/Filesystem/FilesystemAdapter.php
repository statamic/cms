<?php

namespace Statamic\Filesystem;

use Statamic\Facades\Str;
use Symfony\Component\Finder\Finder;
use Illuminate\Filesystem\Filesystem;

class FilesystemAdapter extends AbstractAdapter
{
    protected $root;
    protected $filesystem;

    public function __construct(Filesystem $filesystem, $root)
    {
        $this->filesystem = $filesystem;
        $this->setRootDirectory($root);
    }

    public function setRootDirectory($directory)
    {
        $this->root = Str::ensureRight($directory, '/');

        return $this;
    }

    protected function normalizePath($path)
    {
        if ($path !== '/' && Str::startsWith($path, '/')) {
            return $path;
        }

        if ($path === '.') {
            $path = '/';
        }

        $str = Str::ensureLeft($path, $this->root);

        return Str::trimRight($str, '/');
    }

    public function isDirectory($path)
    {
        return $this->filesystem->isDirectory($this->normalizePath($path));
    }

    public function getFiles($path, $recursive = false)
    {
        $method = $recursive ? 'allFiles' : 'files';

        if (! $this->exists($path)) {
            return [];
        }

        $files = $this->filesystem->$method($this->normalizePath($path), true);

        return collect($files)->map(function ($file) {
            return $this->relativePath($file->getPathname());
        })->all();
    }

    public function getFolders($path, $recursive = false)
    {
        $finder = Finder::create()
            ->in($this->normalizePath($path))
            ->depth($recursive ? '>=0' : 0)
            ->directories();

        return collect($finder)->map(function ($file) {
            return $this->relativePath($file->getPathname());
        })->values()->all();
    }

    public function copyDirectory($src, $dest, $overwrite = false)
    {
        // todo: implement the overwrite argument

        return $this->filesystem->copyDirectory($this->normalizePath($src), $this->normalizePath($dest));
    }

    public function moveDirectory($src, $dest, $overwrite = false)
    {
        return $this->filesystem->moveDirectory($this->normalizePath($src), $this->normalizePath($dest), $overwrite);
    }
}
