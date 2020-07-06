<?php

namespace Statamic\Events\Data;

class AssetFolderSaved extends Saved
{
    public function commitMessage()
    {
        return __('Asset folder saved');
    }
}
