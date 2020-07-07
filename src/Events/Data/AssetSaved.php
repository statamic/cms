<?php

namespace Statamic\Events\Data;

class AssetSaved extends Saved
{
    public function commitMessage()
    {
        return __('Asset saved');
    }
}
