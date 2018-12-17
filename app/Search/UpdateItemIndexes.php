<?php

namespace Statamic\Search;

use Statamic\API\Search;
use Statamic\Events\Data\EntrySaved;
use Statamic\Events\Data\EntryDeleted;

class UpdateItemIndexes
{
    public function subscribe($event)
    {
        $event->listen(EntrySaved::class, self::class . '@update');
        $event->listen(EntryDeleted::class, self::class . '@delete');
    }

    public function update($event)
    {
        $item = $event->data;

        $this->indexes($item)->each(function ($index) use ($item) {
            $index->exists() ? $index->insert($item) : $index->update();
        });
    }

    public function delete($event)
    {
        $item = $event->data;

        $this->indexes($item)->each->delete($item);
    }

    protected function indexes($item)
    {
        return Search::indexes()->filter->shouldIndex($item);
    }
}
