<?php

namespace Statamic\Forms\Uploaders;

use Statamic\Facades\Asset;
use Statamic\Facades\Path;
use Statamic\Support\Arr;

class AssetsUploader
{
    protected $config;

    /**
     * Instantiate assets uploader.
     *
     * @param  array  $config
     */
    public function __construct($config)
    {
        $this->config = collect($config);
    }

    /**
     * Instantiate assets uploader.
     *
     * @param  array  $config
     * @return static
     */
    public static function field($config)
    {
        return new static($config);
    }

    /**
     * Upload the files and return their paths.
     *
     * @param  mixed  $files
     * @return array|string
     */
    public function upload($files)
    {
        $paths = $this->getUploadableFiles($files)->map(function ($file) {
            return $this->createAsset($file)->path();
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

        // If multiple uploads is not enabled for this field, we will
        // simply take the first uploaded file and ignore the rest.
        return $this->isSingleFile()
            ? $files->take(1)
            : $files;
    }

    /**
     * Create an asset from a file.
     *
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile  $file
     * @return \Statamic\Assets\File\Asset
     */
    protected function createAsset($file)
    {
        $path = Path::assemble($this->config->get('folder'), $file->getClientOriginalName());

        $asset = Asset::make()
            ->container($this->config->get('container'))
            ->path(ltrim($path, '/'));

        $asset->upload($file)->save();

        return $asset;
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
