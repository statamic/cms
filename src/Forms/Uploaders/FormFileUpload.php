<?php

namespace Statamic\Forms\Uploaders;

use Statamic\Support\Arr;

/**
 * @phpstan-consistent-constructor
 */
class FormFileUpload
{
    protected $config;

    /**
     * Instantiate form file upload.
     *
     * @param  array  $config
     */
    public function __construct($config, protected string $submissionId)
    {
        $this->config = collect($config);
    }

    /**
     * Instantiate form file upload.
     *
     * @param  array  $config
     * @return static
     */
    public static function field($config, string $submissionId)
    {
        return new static($config, $submissionId);
    }

    /**
     * Upload the files and return their storage paths.
     *
     * @param  mixed  $files
     * @return array|string
     */
    public function upload($files)
    {
        $paths = $this->getUploadableFiles($files)->map(function ($file) {
            return FormFileUploader::submission($this->submissionId, $this->config->get('handle'), $this->config->get('container'))->upload($file);
        });

        return $this->isSingleFile()
            ? $paths->first()
            : $paths->all();
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
