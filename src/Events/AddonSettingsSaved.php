<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class AddonSettingsSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $addon)
    {
    }

    public function commitMessage()
    {
        return __('Addon settings saved', [], config('statamic.git.locale'));
    }
}
