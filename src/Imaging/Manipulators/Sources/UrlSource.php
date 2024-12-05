<?php

namespace Statamic\Imaging\Manipulators\Sources;

class UrlSource extends Source
{
    public function __construct(private readonly string $url)
    {
        //
    }

    public function path(): string
    {
        return $this->url;
    }
}
