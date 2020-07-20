<?php

namespace Statamic\Events;

class AssetSaved extends Saved
{
    public function commitMessage()
    {
        return __('Asset saved');
    }
}
