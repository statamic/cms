<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class DefaultPreferencesSaved extends Event implements ProvidesCommitMessage
{
    public function commitMessage()
    {
        return __('Default preferences saved', [], config('statamic.git.locale'));
    }
}
