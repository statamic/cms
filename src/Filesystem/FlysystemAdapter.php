<?php

namespace Statamic\Filesystem;

use Illuminate\Contracts\Filesystem\Filesystem as FilesystemAdapter;
use RuntimeException;
use Statamic\Facades\Path;
use Statamic\Support\Str;

class FlysystemAdapter extends AbstractAdapter
{
    protected $filesystem;

    public function __construct(FilesystemAdapter $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function normalizePath($path)
    {
        if (! is_string($path)) {
            throw new RuntimeException('Path must be a string.');
        }

        $path = Path::tidy($path);

        if ($path === '' || $path === '/' || $path === '.') {
            return '/';
        }

        if (Path::isAbsolute($path)) {
            $adapter = $this->filesystem->getAdapter();

            // Determine which adapter to use for Flysystem 1.x or 3.x.
            $localClass = class_exists($legacyAdapter = '\League\Flysystem\Adapter\Local')
                ? $legacyAdapter
                : '\League\Flysystem\Local\LocalFilesystemAdapter';

            if (! $adapter instanceof $localClass) {
                throw new \LogicException('Cannot use absolute paths on non-local adapters.');
            }

            if (! Str::startsWith($path, $root = Path::tidy($this->filesystem->path('/')))) {
                throw new \LogicException("Cannot reference path [{$path}] outside the root [{$root}]");
            }

            $path = Str::removeLeft($path, $root);
        }

        return Path::tidy($path);
    }

    public function exists($path)
    {
        // Flysystem wouldn't have let us get this far if the root directory didn't already exist.
        if ($path === '/') {
            return true;
        }

        return parent::exists($path);
    }

    public function getFiles($path, $recursive = false)
    {
        return $this->collection(
            $this->filesystem->files($this->normalizePath($path), $recursive)
        );
    }

    public function getFolders($path, $recursive = false)
    {
        $method = $recursive ? 'allDirectories' : 'directories';

        return collect(
            $this->filesystem->$method($this->normalizePath($path))
        );
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

    public function path($path)
    {
        return $this->filesystem->path($path);
    }

    public function withAbsolutePaths()
    {
        throw new \LogicException('Cannot use absolute paths');
    }
}
