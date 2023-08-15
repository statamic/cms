<?php

namespace Statamic\Assets;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class ReplacementFile
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function path()
    {
        return $this->path;
    }

    public function extension()
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    public function writeTo(Filesystem $disk, $path)
    {
        $disk->put(
            $path,
            Storage::disk('local')->readStream($this->path)
        );
    }
}
