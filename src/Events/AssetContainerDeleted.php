<?php

namespace Statamic\Events;

class AssetContainerDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Asset container deleted');
    }
}
