<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AssetReferencesUpdated extends Event implements ProvidesCommitMessage
{
    public $asset;

    public function __construct($asset)
    {
        $this->asset = $asset;
    }

    public function commitMessage()
    {
        return __('Asset references updated', [], config('statamic.git.locale'));
    }
}
