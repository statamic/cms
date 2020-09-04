<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AssetUploaded extends Event implements ProvidesCommitMessage
{
    public $asset;

    public function __construct($asset)
    {
        $this->asset = $asset;
    }

    public function commitMessage()
    {
        return __('Asset uploaded', [], config('statamic.git.locale'));
    }
}
