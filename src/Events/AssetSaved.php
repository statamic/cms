<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AssetSaved extends Event implements ProvidesCommitMessage
{
    public $asset;
    public $currentUser;

    public function __construct($asset, $currentUser = null)
    {
        $this->asset = $asset;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Asset saved', [], config('statamic.git.locale'));
    }
}
