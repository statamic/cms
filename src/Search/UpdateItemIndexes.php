<?php

namespace Statamic\Search;

use Statamic\Contracts\Taxonomies\Term;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetSaved;
use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;
use Statamic\Events\TermDeleted;
use Statamic\Events\TermSaved;
use Statamic\Events\UserDeleted;
use Statamic\Events\UserSaved;
use Statamic\Facades\Search;

class UpdateItemIndexes
{
    public function subscribe($event)
    {
        $event->listen(EntrySaved::class, self::class.'@update');
        $event->listen(EntryDeleted::class, self::class.'@delete');
        $event->listen(AssetSaved::class, self::class.'@update');
        $event->listen(AssetDeleted::class, self::class.'@delete');
        $event->listen(UserSaved::class, self::class.'@update');
        $event->listen(UserDeleted::class, self::class.'@delete');
        $event->listen(TermSaved::class, self::class.'@update');
        $event->listen(TermDeleted::class, self::class.'@delete');
    }

    public function update($event)
    {
        $this->items($event)->each(fn ($item) => Search::updateWithinIndexes($item));
    }

    public function delete($event)
    {
        $this->items($event)->each(fn ($item) => Search::deleteFromIndexes($item));
    }

    private function items($event)
    {
        $item = $event->entry ?? $event->asset ?? $event->user ?? $event->term;

        if ($item instanceof Term) {
            $items = $item->localizations();
        } else {
            $items = collect([$item]);
        }

        return $items;
    }
}
