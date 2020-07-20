<?php

namespace Statamic\Events;

class AssetContainerSaved extends Saved
{
    public function commitMessage()
    {
        return __('Asset container saved');
    }
}
