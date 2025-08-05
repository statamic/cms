<?php

namespace Statamic\Entries;

use Statamic\Contracts\Entries\Entry;
use Statamic\Events\EntryBlueprintFound;

class ChangeAuthorFieldVisibility
{
    public function handle(EntryBlueprintFound $event)
    {
        if (! $event->entry) {
            return;
        }

        if (! $event->authenticatedUser) {
            return;
        }

        $authorVisibility = match (true) {
            $event->authenticatedUser->cant('view-other-authors-entries', [Entry::class, $event->entry->collection()]) => 'hidden',
            $event->authenticatedUser->cant('edit-other-authors-entries', [Entry::class, $event->entry->collection(), $event->blueprint]) => 'read_only',
            default => 'visible',
        };

        $event->blueprint->ensureFieldHasConfig('author', ['visibility' => $authorVisibility]);
    }
}
