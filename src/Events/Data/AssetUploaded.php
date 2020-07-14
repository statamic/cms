<?php

namespace Statamic\Events\Data;

class AssetUploaded extends Saved
{
    public function commitMessage()
    {
        return __('Asset uploaded');
    }
}
