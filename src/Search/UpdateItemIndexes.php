<?php

namespace Statamic\Search;

use Statamic\Events\Data\EntryDeleted;
use Statamic\Events\Data\EntrySaved;
use Statamic\Facades\Search;

class UpdateItemIndexes
{
    public function subscribe($event)
    {
        $event->listen(EntrySaved::class, self::class.'@update');
        $event->listen(EntryDeleted::class, self::class.'@delete');
    }

    public function update($event)
    {
        $item = $event->item;

        $this->indexes($item)->each(function ($index) use ($item) {
            $index->exists() ? $index->insert($item) : $index->update();
        });
    }

    public function delete($event)
    {
        $item = $event->item;

        $this->indexes($item)->each->delete($item);
    }

    protected function indexes($item)
    {
        return Search::indexes()->filter->shouldIndex($item);
    }
}
