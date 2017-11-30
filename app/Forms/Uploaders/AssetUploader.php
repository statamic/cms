<?php

namespace Statamic\Forms\Uploaders;

use Statamic\API\Asset;
use Statamic\API\Path;

class AssetUploader extends Uploader
{
    /**
     * Upload the files and return their ids.
     *
     * @return array|string
     */
    public function upload()
    {
        $ids = $this->files->map(function ($file) {
            return $this->createAsset($file)->url();
        });

        return ($this->multipleFilesAllowed()) ? $ids->all() : $ids->first();
    }

    /**
     * Create an asset from a file
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return \Statamic\Assets\File\Asset
     */
    private function createAsset($file)
    {
        $path = Path::assemble($this->config->get('folder'), $file->getClientOriginalName());

        $asset = Asset::create()
                      ->container($this->config->get('container'))
                      ->path(ltrim($path, '/'))
                      ->get();

        $asset->upload($file);

        $asset->save();

        return $asset;
    }

    /**
     * Are multiple files allowed to be uploaded?
     *
     * @return bool
     */
    protected function multipleFilesAllowed()
    {
        return $this->config->get('type') === 'assets';
    }
}
