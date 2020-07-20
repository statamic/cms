<?php

namespace Statamic\Events;

class AssetUploaded extends Saved
{
    public $asset;

    public function __construct($asset)
    {
        $this->asset = $asset;
    }

    public function commitMessage()
    {
        return __('Asset uploaded');
    }
}
