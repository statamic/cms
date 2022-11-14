<?php

namespace Statamic\Git;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\ListensForContentEvents;
use Statamic\Events\Subscriber as StatamicSubscriber;
use Statamic\Facades\Git;

class Subscriber extends StatamicSubscriber
{
    use ListensForContentEvents;

    /**
     * Map subscribable listeners.
     *
     * @return array
     */
    protected function getListeners()
    {
        return collect($this->events)
            ->mapWithKeys(function ($event) {
                return [$event => static::class.'@commit'];
            })
            ->all();
    }

    /**
     * Commit changes.
     *
     * @param  mixed  $event
     */
    public function commit($event)
    {
        if ($this->automaticGitIsDisabled() || $this->eventIsIgnored($event)) {
            return;
        }

        Git::dispatchCommit(
            $event instanceof ProvidesCommitMessage
                ? $event->commitMessage()
                : null
        );
    }

    /**
     * Check if automatic git is disabled.
     *
     * @return bool
     */
    protected function automaticGitIsDisabled()
    {
        return ! (config('statamic.git.enabled') && config('statamic.git.automatic'));
    }

    /**
     * Check if event is ignored.
     *
     * @param  mixed  $event
     * @return bool
     */
    protected function eventIsIgnored($event)
    {
        return collect(config('statamic.git.ignored_events'))->contains(get_class($event));
    }
}
