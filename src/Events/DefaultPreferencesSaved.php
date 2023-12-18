<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class DefaultPreferencesSaved extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

    public function commitMessage()
    {
        return __('Default preferences saved', [], config('statamic.git.locale'));
    }
}
