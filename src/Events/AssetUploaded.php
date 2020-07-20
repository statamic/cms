<?php

namespace Statamic\Events;

class AssetUploaded extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Asset uploaded');
    }
}
