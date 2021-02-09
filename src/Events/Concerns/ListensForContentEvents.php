<?php

namespace Statamic\Events\Concerns;

trait ListensForContentEvents
{
    /**
     * Content changed events.
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
}
