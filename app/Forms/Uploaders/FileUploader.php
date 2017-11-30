<?php

namespace Statamic\Forms\Uploaders;

use Statamic\API\File;

class FileUploader extends Uploader
{
    /**
     * Upload the files and return their paths.
     *
     * @return array|string
     */
    public function upload()
    {
        $paths = $this->files->map(function ($file) {
            $destination = $this->getDestination($file);

            $this->uploadFile($file, $destination);

            return pathinfo($destination)['basename'];
        });

        return ($this->multipleFilesAllowed()) ? $paths->all() : $paths->first();
    }

    /**
     * Upload a file
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @param string $destination
     */
    private function uploadFile($file, $destination)
    {
        $stream = fopen($file->getRealPath(), 'r+');

        File::put($destination, $stream);

        fclose($stream);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return string
     */
    private function getDestination($file)
    {
        $basename = $file->getClientOriginalName();
        $filename = pathinfo($basename)['filename'];
        $destination = $this->config->get('destination');
        $path = $destination . '/' . $basename;

        if (File::exists($path)) {
            $basename = $filename . '-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $destination . '/' . $basename;
        }

        return $path;
    }

    /**
     * Are multiple files allowed to be uploaded?
     *
     * @return bool
     */
    protected function multipleFilesAllowed()
    {
        return $this->config->get('type') === 'files';
    }
}