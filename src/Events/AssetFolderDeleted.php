<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class AssetFolderDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $folder)
    {
    }

    public function commitMessage()
    {
        return __('Asset folder deleted', [], config('statamic.git.locale'));
    }
}
