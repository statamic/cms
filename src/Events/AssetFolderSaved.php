<?php

namespace Statamic\Events;

class AssetFolderSaved extends Saved
{
    public function commitMessage()
    {
        return __('Asset folder saved');
    }
}
