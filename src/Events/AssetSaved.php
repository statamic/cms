<?php

namespace Statamic\Events;

class AssetSaved extends Saved
{
    public $asset;

    public function __construct($asset)
    {
        $this->asset = $asset;
    }

    public function commitMessage()
    {
        return __('Asset saved');
    }
}
