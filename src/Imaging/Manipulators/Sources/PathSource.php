<?php

namespace Statamic\Imaging\Manipulators\Sources;

use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Asset as Assets;

class PathSource extends Source
{
    public function __construct(private readonly string $path)
    {
        //
    }

    public function path(): string
    {
        return $this->path;
    }

    public function asset(): ?Asset
    {
        return Assets::findByUrl(str($this->path)->start('/'));
    }
}
