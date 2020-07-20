<?php

namespace Statamic\Events;

class AssetDeleted extends Deleted
{
    public $asset;

    public function __construct($asset)
    {
        $this->asset = $asset;
    }

    public function commitMessage()
    {
        return __('Asset deleted');
    }
}
