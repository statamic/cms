<?php

namespace Statamic\Assets;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use League\Flysystem\DirectoryListing;
use Statamic\Statamic;
use Statamic\Support\Str;

class AssetContainerContents
{
    protected $container;
    protected $files;
    protected $metaFiles;
    protected $filteredFiles;
    protected $filteredDirectories;

    public function container($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get all asset container contents.
     *
     * @return Collection
     */
    public function all()
    {
        if ($this->files && ! Statamic::isWorker()) {
            return $this->files;
        }

        return $this->files = Cache::remember($this->key(), $this->ttl(), function () {
            return collect($this->getRawFlysystemDirectoryListing())
                ->keyBy('path')
                ->map(fn ($file) => $this->normalizeFlysystemAttributes($file))
                ->pipe(fn ($files) => $this->ensureMissingDirectoriesExist($files))
                ->sortKeys();
        });
    }

    /**
     * Flysystem's `DirectoryListing` gives us type, timestamps, dirname, and will allow us perform more efficient filtering, caching, etc.
     */
    private function getRawFlysystemDirectoryListing(): DirectoryListing
    {
        return $this->filesystem()->listContents('/', true);
    }

    /**
     * Normalize `FileAttributes` and `DirectoryAttributes` payloads to the legacy Statamic array style.
     *
     * @param  mixed  $attributes
     * @return array
     */
    private function normalizeFlysystemAttributes($attributes)
    {
        // Merge attributes with `pathinfo()`.
        $normalized = array_merge([
            'type' => $attributes->type(),
            'path' => $attributes->path(),
            'timestamp' => $attributes->lastModified(),
        ], pathinfo($attributes['path']));

        // Flysystem 3+ now returns `.` dirnames, but the rest of our system expects an empty string.
        if (isset($normalized['dirname']) && $normalized['dirname'] === '.') {
            $normalized['dirname'] = '';
        }

        // Only return `size` if type is file.
        if ($normalized['type'] === 'file') {
            $normalized['size'] = $attributes->fileSize();
        }

        return $normalized;
    }

    /**
     * Ensure missing directories exist.
     *
     * Note: S3 doesn't always return directories as part of the listings, so
     * this method ensures we get consistent results with S3 filesystems.
     * For more info, see: https://github.com/statamic/cms/pull/7205
     */
    private function ensureMissingDirectoriesExist(Collection $files): Collection
    {
        $files
            ->filter(fn ($item) => $item['type'] === 'file')
            ->each(function ($file) use ($files) {
                $dirname = $file['dirname'];

                while ($dirname !== '') {
                    $parentDir = pathinfo($dirname, PATHINFO_DIRNAME);
                    $parentDir = $parentDir === '.' ? '' : $parentDir;

                    $files->put($dirname, [
                        'type' => 'dir',
                        'path' => $dirname,
                        'basename' => $basename = pathinfo($dirname, PATHINFO_BASENAME),
                        'filename' => $basename,
                        'timestamp' => null,
                        'dirname' => $parentDir,
                    ]);

                    $dirname = $parentDir;
                }
            });

        return $files;
    }

    /**
     * Get normalized flysystem meta data.
     *
     * @param  string  $path
     * @return array
     */
    private function getNormalizedFlysystemMetadata($path)
    {
        // Use exception handling to avoid another `has()` API method call if possible.
        try {
            $timestamp = $this->filesystem()->lastModified($path);
        } catch (\Exception $exception) {
            $timestamp = null;
        }

        // Only perform explicit `has()` API file existence check as a fallback
        // if timestamp ends up as null. This is needed when `add()`ing new
        // directories to the files cache with the flysystem S3 driver.
        if ($timestamp === null && $this->filesystem()->has($path) === false) {
            return false;
        }

        // Use exception handling to normalize file size output.
        try {
            $size = $this->filesystem()->fileSize($path);
        } catch (\Exception $exception) {
            $size = false;
        }

        // Determine `type` off returned size to avoid another API method call.
        $type = $size === false ? 'dir' : 'file';

        // Merge attributes with `pathinfo()`.
        $normalized = array_merge(compact('type', 'path', 'timestamp'), pathinfo($path));

        // Flysystem 3+ now returns `.` dirnames, but the rest of our system expects an empty string.
        if (isset($normalized['dirname']) && $normalized['dirname'] === '.') {
            $normalized['dirname'] = '';
        }

        // Only return `size` if type is file.
        if ($type === 'file') {
            $normalized['size'] = $size;
        }

        return $normalized;
    }

    public function cached()
    {
        return Cache::get($this->key());
    }

    public function files()
    {
        return $this->all()->where('type', 'file');
    }

    public function directories()
    {
        return $this->all()->where('type', 'dir');
    }

    public function metaFilesIn($folder, $recursive)
    {
        if (isset($this->metaFiles[$key = $folder.($recursive ? '-recursive' : '')])) {
            return $this->metaFiles[$key];
        }

        $files = $this->files();

        $files = $files->filter(function ($file, $path) {
            return Str::startsWith($path, '.meta/')
                || Str::contains($path, '/.meta/');
        });

        // Filter by folder and recursiveness. But don't bother if we're
        // requesting the root recursively as it's already that way.
        if ($folder === '/' && $recursive) {
            //
        } else {
            $files = $files->filter(function ($file) use ($folder, $recursive) {
                $dir = $file['dirname'];
                $dir = substr($dir, 0, -6); // remove .meta/ from the end
                $dir = $dir ?: '/';

                return $recursive ? Str::startsWith($dir, $folder) : $dir == $folder;
            });
        }

        return $this->metaFiles[$key] = $files;
    }

    public function filteredFilesIn($folder, $recursive)
    {
        if (isset($this->filteredFiles[$key = $folder.($recursive ? '-recursive' : '')]) && ! Statamic::isWorker()) {
            return $this->filteredFiles[$key];
        }

        $files = $this->files();

        // Filter by folder and recursiveness. But don't bother if we're
        // requesting the root recursively as it's already that way.
        if ($folder === '/' && $recursive) {
            //
        } else {
            $files = $files->filter(function ($file) use ($folder, $recursive) {
                $dir = $file['dirname'] ?: '/';

                return $recursive ? Str::startsWith($dir, $folder) : $dir == $folder;
            });
        }

        // Get rid of files we never want to show up.
        $files = $files->reject(function ($file, $path) {
            return Str::startsWith($path, '.meta/')
                || Str::contains($path, '/.meta/')
                || Str::endsWith($path, ['.DS_Store', '.gitkeep', '.gitignore']);
        });

        return $this->filteredFiles[$key] = $files;
    }

    public function filteredDirectoriesIn($folder, $recursive)
    {
        if (isset($this->filteredDirectories[$key = $folder.($recursive ? '-recursive' : '')]) && ! Statamic::isWorker()) {
            return $this->filteredDirectories[$key];
        }

        $files = $this->directories();

        // Filter by folder and recursiveness. But don't bother if we're
        // requesting the root recursively as it's already that way.
        if ($folder === '/' && $recursive) {
            //
        } else {
            $files = $files->filter(function ($file) use ($folder, $recursive) {
                $dir = $file['dirname'] ?: '/';

                return $recursive ? Str::startsWith($dir, $folder) : $dir == $folder;
            });
        }

        $files = $files->reject(function ($file) {
            return $file['basename'] == '.meta';
        });

        return $this->filteredDirectories[$key] = $files;
    }

    private function filesystem()
    {
        return $this->container->disk()->filesystem()->getDriver();
    }

    public function save()
    {
        Cache::put($this->key(), $this->all(), $this->ttl());
    }

    public function forget($path)
    {
        $this->files = $this->all()->forget($path);

        $this->filteredFiles = null;
        $this->filteredDirectories = null;

        return $this;
    }

    public function add($path)
    {
        if (! $metadata = $this->getNormalizedFlysystemMetadata($path)) {
            return $this;
        }

        // Add parent directories
        if (($dir = dirname($path)) !== '.') {
            $this->add($dir);
        }

        $files = $this->all()->put($path, $metadata);

        if (Statamic::isWorker()) {
            Cache::put($this->key(), $files, $this->ttl());
        }

        $this->filteredFiles = null;
        $this->filteredDirectories = null;

        return $this;
    }

    private function key()
    {
        return 'asset-list-contents-'.$this->container->handle();
    }

    private function ttl()
    {
        return config('statamic.stache.watcher') ? 0 : null;
    }
}
