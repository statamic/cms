<?php

namespace Statamic\Forms\Uploaders;

use Statamic\Exceptions\AssetContainerNotFoundException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Path;
use Statamic\Fieldtypes\Assets\UndefinedContainerException;
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
     * Upload the files and return their IDs.
     *
     * @param  mixed  $files
     * @return array|string
     */
    public function upload($files)
    {
        $ids = $this->getUploadableFiles($files)->map(function ($file) {
            return $this->createAsset($file)->id();
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
     * Create an asset from a file.
     *
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile  $file
     * @return \Statamic\Assets\File\Asset
     */
    protected function createAsset($file)
    {
        $path = Path::assemble($this->config->get('folder'), $file->getClientOriginalName());

        $asset = Asset::make()
            ->container($this->assetContainer())
            ->path(ltrim($path, '/'));

        $asset->upload($file)->save();

        return $asset;
    }

    /**
     * Find the asset container.
     *
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    protected function assetContainer()
    {
        if ($configured = $this->config->get('container')) {
            if ($container = AssetContainer::find($configured)) {
                return $container;
            }

            throw new AssetContainerNotFoundException($configured);
        }

        if (($containers = AssetContainer::all())->count() === 1) {
            return $containers->first();
        }

        throw new UndefinedContainerException;
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
