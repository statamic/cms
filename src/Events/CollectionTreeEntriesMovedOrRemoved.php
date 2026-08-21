<?php

namespace Statamic\Events;

use Statamic\Facades\Entry;

class CollectionTreeEntriesMovedOrRemoved extends Event
{
    public array $removedUrls;
    public array $movedUrls;

    public function __construct(
        public array $removed,
        public array $moved,
    ) {
        $this->removedUrls = $this->resolveUrls($removed);
        $this->movedUrls = $this->resolveUrls($moved);
    }

    private function resolveUrls(array $ids): array
    {
        return collect($ids)
            ->flatMap(function ($id) {
                if (! $entry = Entry::find($id)) {
                    return [];
                }

                return $entry->descendants()
                    ->merge([$entry])
                    ->reject->isRedirect()
                    ->map->absoluteUrl()
                    ->filter()
                    ->values()
                    ->all();
            })
            ->unique()
            ->values()
            ->all();
    }
}
