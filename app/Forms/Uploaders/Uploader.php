<?php

namespace Statamic\Forms\Uploaders;

abstract class Uploader
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $config;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $files;

    /**
     * @param $config
     * @param $files
     */
    public function __construct($config, $files)
    {
        $this->config = collect($config);
        $this->files = $files;

        // If multiple uploads is not enabled for this field, we will
        // simply take the first uploaded file and ignore the rest.
        if (! $this->multipleFilesAllowed()) {
            $this->files = collect([$this->files->first()]);
        }
    }

    /**
     * Are multiple files allowed to be uploaded?
     *
     * @return bool
     */
    abstract protected function multipleFilesAllowed();
}