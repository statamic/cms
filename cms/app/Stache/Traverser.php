<?php

namespace Statamic\Stache;

use League\Flysystem\Filesystem;
use Illuminate\Support\Collection;

class Traverser
{
    /**
     * @var \League\Flysystem\Filesystem
     */
    private $filesystem;

    /**
     * @var Collection
     */
    private $files;

    /**
     * @var Collection
     */
    private $timestamps;

    /**
     * @var Collection
     */
    private $modified_files;

    /**
     * @var Collection
     */
    private $deleted_files;

    /**
     * @var Driver
     */
    private $driver;

    /**
     * @param \Statamic\Stache\Driver $driver
     * @param \League\Flysystem\Filesystem     $filesystem
     */
    public function __construct(Driver $driver, Filesystem $filesystem)
    {
        $this->driver = $driver;
        $this->filesystem = $filesystem;
    }

    /**
     * Perform the traversal
     *
     * @return void
     */
    public function traverse()
    {
        $this->setAllFiles();
        $this->setModifiedFiles();
        $this->setDeletedFiles();
        $this->addContentsToModifiedFiles();
    }

    /**
     * Get the modified files
     *
     * @return array
     */
    public function modified()
    {
        return $this->modified_files->map(function ($file) {
            return $file['contents'];
        })->all();
    }

    /**
     * Get the deleted files
     *
     * @return array
     */
    public function deleted()
    {
        return $this->deleted_files->values()->all();
    }

    public function timestamps($timestamps = null)
    {
        if (is_null($timestamps)) {
            return $this->files->pluck('timestamp', 'path');
        }

        $this->timestamps = collect($timestamps);
    }

    /**
     * Go through the content directory and find all the files
     *
     * @return void
     */
    private function setAllFiles()
    {
        $this->files = collect(
            $this->filesystem->listContents($this->driver->getFilesystemRoot(), $this->driver->traverseRecursively())
        )->filter(function ($file) {
            // Always ignore these annoying files.
            if (in_array($file['basename'], ['.DS_Store'])) {
                return false;
            }

            return $this->driver->isMatchingFile($file);
        })->keyBy(function ($file) {
            return $file['path'];
        });
    }

    /**
     * Work out which files have been modified
     *
     * @return mixed
     */
    private function setModifiedFiles()
    {
        $this->modified_files = $this->files->filter(function ($file) {
            // The file didn't previously exist? Must be new.
            if (! $this->timestamps->has($file['path'])) {
                return true;
            }

            // If the existing timestamp is less/older, it's modified.
            return $this->timestamps->get($file['path']) < $file['timestamp'];
        });
    }

    /**
     * Work out which files have been deleted
     *
     * @return void
     */
    private function setDeletedFiles()
    {
        $this->deleted_files = $this->timestamps->keys()
            ->reject(function ($path) {
                return $this->files->has($path);
            });
    }

    /**
     * Read the contents of all modified files and add the the collection
     *
     * @return void
     */
    private function addContentsToModifiedFiles()
    {
        $this->modified_files = $this->modified_files->map(function ($file) {
            $file['contents'] = $this->filesystem->read($file['path']);
            return $file;
        });
    }
}