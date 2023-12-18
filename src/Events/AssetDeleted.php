<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AssetDeleted extends Event implements ProvidesCommitMessage
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
        return __('Asset deleted', [], config('statamic.git.locale'));
    }
}
