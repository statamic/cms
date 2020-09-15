<?php

namespace Statamic\Git;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Facades\Git;

class Subscriber
{
    /**
     * Subscribed events.
     *
     * @var array
     */
    protected $events = [
        \Statamic\Events\AssetContainerDeleted::class,
        \Statamic\Events\AssetContainerSaved::class,
        \Statamic\Events\AssetDeleted::class,
        \Statamic\Events\AssetFolderDeleted::class,
        \Statamic\Events\AssetFolderSaved::class,
        \Statamic\Events\AssetSaved::class,
        \Statamic\Events\BlueprintDeleted::class,
        \Statamic\Events\BlueprintSaved::class,
        \Statamic\Events\CollectionDeleted::class,
        \Statamic\Events\CollectionSaved::class,
        \Statamic\Events\EntryDeleted::class,
        \Statamic\Events\EntrySaved::class,
        \Statamic\Events\FieldsetDeleted::class,
        \Statamic\Events\FieldsetSaved::class,
        \Statamic\Events\FormDeleted::class,
        \Statamic\Events\FormSaved::class,
        \Statamic\Events\GlobalSetDeleted::class,
        \Statamic\Events\GlobalSetSaved::class,
        \Statamic\Events\NavDeleted::class,
        \Statamic\Events\NavSaved::class,
        \Statamic\Events\RoleDeleted::class,
        \Statamic\Events\RoleSaved::class,
        \Statamic\Events\SubmissionDeleted::class,
        \Statamic\Events\SubmissionSaved::class,
        \Statamic\Events\TaxonomyDeleted::class,
        \Statamic\Events\TaxonomySaved::class,
        \Statamic\Events\TermDeleted::class,
        \Statamic\Events\TermSaved::class,
        \Statamic\Events\UserDeleted::class,
        \Statamic\Events\UserGroupDeleted::class,
        \Statamic\Events\UserGroupSaved::class,
        \Statamic\Events\UserSaved::class,
    ];

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        foreach ($this->events as $event) {
            $events->listen($event, self::class.'@commit');
        }
    }

    /**
     * Commit changes.
     *
     * @param mixed $event
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
     * @param mixed $event
     * @return bool
     */
    protected function eventIsIgnored($event)
    {
        return collect(config('statamic.git.ignored_events'))->contains(get_class($event));
    }
}
