<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AssetFolderSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $folder)
    {
    }

    public function commitMessage()
    {
        return __('Asset folder saved', [], config('statamic.git.locale'));
    }
}
