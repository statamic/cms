<?php

namespace Statamic\Events;

class AssetUploaded extends Saved
{
    public function commitMessage()
    {
        return __('Asset uploaded');
    }
}
