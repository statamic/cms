<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class AssetDeleted extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

    public $asset;

    public function __construct($asset)
    {
        $this->asset = $asset;
    }

    public function commitMessage()
    {
        return __('Asset deleted', [], config('statamic.git.locale'));
    }
}
