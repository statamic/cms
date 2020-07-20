<?php

namespace Statamic\Events;

class AssetContainerDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Asset container deleted');
    }
}
