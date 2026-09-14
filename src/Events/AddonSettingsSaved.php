<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class AddonSettingsSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $settings)
    {
    }

    public function commitMessage()
    {
        return __('Addon settings saved', [], config('statamic.git.locale'));
    }
}
