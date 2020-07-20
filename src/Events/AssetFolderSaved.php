<?php

namespace Statamic\Events;

class AssetFolderSaved extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Asset folder saved');
    }
}
