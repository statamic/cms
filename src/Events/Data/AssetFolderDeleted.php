<?php

namespace Statamic\Events\Data;

class AssetFolderDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Asset folder deleted');
    }
}
