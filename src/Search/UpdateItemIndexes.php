<?php

namespace Statamic\Search;

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
        $item = $event->entry ?? $event->asset ?? $event->user ?? $event->term;

        $this->indexes($item)->each(function ($index) use ($item) {
            $index->exists() ? $index->insert($item) : $index->update();
        });
    }

    public function delete($event)
    {
        $item = $event->entry ?? $event->asset ?? $event->user ?? $event->term;

        $this->indexes($item)->each->delete($item);
    }

    protected function indexes($item)
    {
        return Search::indexes()->filter->shouldIndex($item);
    }
}
