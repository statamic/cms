<?php

namespace Statamic\Events;

class AssetSaved extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Asset saved');
    }
}
