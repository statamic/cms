<?php

namespace Statamic\Forms\Uploaders;

use Statamic\Assets\FileUploader;
use Statamic\Support\Arr;

class FilesUploader
{
    protected $config;

    /**
     * Instantiate files uploader.
     *
     * @param  array  $config
     */
    public function __construct($config)
    {
        $this->config = collect($config);
    }

    /**
     * Instantiate files uploader.
     *
     * @param  array  $config
     * @return static
     */
    public static function field($config)
    {
        return new static($config);
    }

    /**
     * Upload the files and return their IDs.
     *
     * @param  mixed  $files
     * @return array|string
     */
    public function upload($files)
    {
        $ids = $this->getUploadableFiles($files)->map(function ($file) {
            return FileUploader::container()->upload($file);
        });

        return $this->isSingleFile()
            ? $ids->first()
            : $ids->all();
    }

    /**
     * Get uploadable files.
     *
     * @param  mixed  $files
     * @return \Illuminate\Support\Collection
     */
    protected function getUploadableFiles($files)
    {
        $files = collect(Arr::wrap($files))->filter();

        // If multiple uploads is not enabled for this field, we will
        // simply take the first uploaded file and ignore the rest.
        return $this->isSingleFile()
            ? $files->take(1)
            : $files;
    }

    /**
     * Determine if uploader should only upload a single file.
     *
     * @return bool
     */
    protected function isSingleFile()
    {
        return $this->config->get('max_files') === 1;
    }
}
