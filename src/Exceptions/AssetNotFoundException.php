<?php

namespace Statamic\Exceptions;

class AssetNotFoundException extends \Exception
{
    protected $asset;

    public function __construct($asset)
    {
        parent::__construct("Asset [{$asset}] not found");

        $this->asset = $asset;
    }
}
