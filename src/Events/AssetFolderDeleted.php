<?php

namespace Statamic\Events;

class AssetFolderDeleted extends Deleted
{
    public $folder;

    public function __construct($folder)
    {
        $this->folder = $folder;
    }

    public function commitMessage()
    {
        return __('Asset folder deleted');
    }
}
