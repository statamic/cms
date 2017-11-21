<?php

namespace Statamic\Filesystem;

use Statamic\API\Str;
use Statamic\API\Path;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

/**
 * Provides methods to manipulate files
 */
class FileAccessor
{
    private $disk;
    private $filesystem;

    public function __construct($disk, \Illuminate\Contracts\Filesystem\Filesystem $filesystem)
    {
        $this->disk = $disk;
        $this->filesystem = $filesystem;
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function filesystem()
    {
        return $this->filesystem;
    }

    /**
     * Get a file's contents
     *
     * @param string $file      Path to the file
     * @param string $fallback  A fallback string
     * @return string
     */
    public function get($file, $fallback = null)
    {
        try {
            return $this->filesystem->get(Path::makeRelative($file));
        } catch (FileNotFoundException $e) {
            return $fallback;
        }
    }

    /**
     * Check if a file exists
     *
     * @param string $file  Path to the file
     * @return bool
     */
    public function exists($file)
    {
        return $this->filesystem->exists(Path::makeRelative($file));
    }

    /**
     * Put content into a file
     *
     * @param string $file      Path to file
     * @param string $contents  Contents to be written
     * @return bool
     */
    public function put($file, $contents)
    {
        return $this->filesystem->put(Path::makeRelative($file), $contents);
    }

    /**
     * Copy a file
     *
     * @param string $src  Path to source file
     * @param string $dest Path to destination
     * @param bool   $overwrite Should the destination be overwritten if already exists?
     * @return bool
     */
    public function copy($src, $dest, $overwrite = false)
    {
        $dest = Path::makeRelative($dest);

        if ($overwrite && $this->exists($dest)) {
            $this->delete($dest);
        }

        return $this->filesystem->copy(Path::makeRelative($src), $dest);
    }

    /**
     * Move a file
     *
     * @param string $src  Path to source file
     * @param string $dest Path to destination
     * @param bool   $overwrite Should the destination be overwritten if already exists?
     * @return bool
     */
    public function move($src, $dest, $overwrite = false)
    {
        $dest = Path::makeRelative($dest);

        if ($overwrite && $this->exists($dest)) {
            $this->delete($dest);
        }

        return $this->filesystem->move(Path::makeRelative($src), $dest);
    }

    /**
     * Delete a file
     *
     * @param string $file Path to file
     * @return bool
     * @throws \Exception
     */
    public function delete($file)
    {
        if (is_null($file)) {
            throw new \Exception('No file specified for deletion.');
        }

        return $this->filesystem->delete(Path::makeRelative($file));
    }

    /**
     * Rename a file
     *
     * @param string $old  Old filename
     * @param string $new  New filename
     * @return bool
     */
    public function rename($old, $new)
    {
        $old = Path::makeRelative($old);
        $new = Path::makeRelative($new);

        return $this->filesystem->move($old, $new);
    }

    /**
     * Extract the file extension from a file path.
     *
     * @param  string  $path  Path of file to extract
     * @return string
     */
    public function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Get the MIME type of a file
     *
     * @param string $path  Path to file
     * @return string
     */
    public function mimeType($path)
    {
        return $this->filesystem->mimeType(Path::makeRelative($path));
    }

    /**
     * Get the last modified timestamp
     *
     * @param string $file  Path to file
     * @return int
     */
    public function lastModified($file)
    {
        return $this->filesystem->lastModified(Path::makeRelative($file));
    }

    /**
     * Get the size of the file, in bytes
     *
     * @param  string $file  Path to file
     * @return int
     */
    public function size($file)
    {
        return $this->filesystem->size(Path::makeRelative($file));
    }

    /**
     * Get the human file size of a given file.
     *
     * @param string $file Path to file
     * @return string
     */
    public function sizeHuman($file)
    {
        return Str::fileSizeForHumans($this->size($file), 2);
    }

    /**
     * Determine if a file is an image
     *
     * @param  string $file File to evaluate
     * @return bool
     */
    public function isImage($file)
    {
        return in_array($this->extension($file), ['jpg', 'jpeg', 'png', 'gif']);
    }
}
