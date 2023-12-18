<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class AssetUploaded extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

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
