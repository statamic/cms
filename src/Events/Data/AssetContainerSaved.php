<?php

namespace Statamic\Events\Data;

class AssetContainerSaved extends Saved
{
    public function commitMessage()
    {
        return __('Asset container saved');
    }
}
