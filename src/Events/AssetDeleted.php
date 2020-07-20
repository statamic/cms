<?php

namespace Statamic\Events;

class AssetDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Asset deleted');
    }
}
