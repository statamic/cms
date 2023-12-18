<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AssetFolderSaved extends Event implements ProvidesCommitMessage
{
    public $folder;
    public $currentUser;

    public function __construct($folder, $currentUser = null)
    {
        $this->folder = $folder;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Asset folder saved', [], config('statamic.git.locale'));
    }
}
