<?php

namespace Statamic\Events\Data;

class AssetContainerDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Asset container deleted');
    }
}
