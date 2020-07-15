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
        \Statamic\Events\Data\AssetContainerDeleted::class,
        \Statamic\Events\Data\AssetContainerSaved::class,
        \Statamic\Events\Data\AssetDeleted::class,
        \Statamic\Events\Data\AssetFolderDeleted::class,
        \Statamic\Events\Data\AssetFolderSaved::class,
        \Statamic\Events\Data\AssetSaved::class,
        \Statamic\Events\Data\BlueprintDeleted::class,
        \Statamic\Events\Data\BlueprintSaved::class,
        \Statamic\Events\Data\CollectionDeleted::class,
        \Statamic\Events\Data\CollectionSaved::class,
        \Statamic\Events\Data\EntryDeleted::class,
        \Statamic\Events\Data\EntrySaved::class,
        \Statamic\Events\Data\FieldsetDeleted::class,
        \Statamic\Events\Data\FieldsetSaved::class,
        \Statamic\Events\Data\FormDeleted::class,
        \Statamic\Events\Data\FormSaved::class,
        \Statamic\Events\Data\GlobalSetDeleted::class,
        \Statamic\Events\Data\GlobalSetSaved::class,
        \Statamic\Events\Data\NavDeleted::class,
        \Statamic\Events\Data\NavSaved::class,
        \Statamic\Events\Data\RoleDeleted::class,
        \Statamic\Events\Data\RoleSaved::class,
        \Statamic\Events\Data\SubmissionDeleted::class,
        \Statamic\Events\Data\SubmissionSaved::class,
        \Statamic\Events\Data\TaxonomyDeleted::class,
        \Statamic\Events\Data\TaxonomySaved::class,
        \Statamic\Events\Data\TermDeleted::class,
        \Statamic\Events\Data\TermSaved::class,
        \Statamic\Events\Data\UserDeleted::class,
        \Statamic\Events\Data\UserGroupDeleted::class,
        \Statamic\Events\Data\UserGroupSaved::class,
        \Statamic\Events\Data\UserSaved::class,
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
