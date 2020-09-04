<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AssetFolderDeleted extends Event implements ProvidesCommitMessage
{
    public $folder;

    public function __construct($folder)
    {
        $this->folder = $folder;
    }

    public function commitMessage()
    {
        return __('Asset folder deleted', [], config('statamic.git.locale'));
    }
}
