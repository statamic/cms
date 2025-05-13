<?php

namespace Statamic\Assets;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;

class UploadedReplacementFile extends ReplacementFile
{
    public function __construct(private UploadedFile $file)
    {
    }

    public function extension()
    {
        return $this->file->getClientOriginalExtension();
    }

    public function writeTo(Filesystem $disk, $path)
    {
        $disk->putFileAs($this->file, $path);
    }
}
