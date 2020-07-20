<?php

namespace Statamic\Events;

class AssetFolderDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Asset folder deleted');
    }
}
