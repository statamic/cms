<?php

namespace Statamic\Imaging\Manipulators\Sources;

use Statamic\Facades\Asset;

class AssetIdSource extends AssetSource
{
    public function __construct(string $source)
    {
        parent::__construct(Asset::findOrFail($source));
    }
}
