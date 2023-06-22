<?php

namespace Statamic\Stache\Indexes\Entries;

use Illuminate\Support\Carbon;
use Statamic\Facades\Entry;
use Statamic\Stache\Indexes\Value;

class Status extends Value
{
    private $recentlyUpdated = false;

    public function getItemValue($entry)
    {
        $status = $entry->status();

        return $status.'::'.$this->getExpiration($entry, $status);
    }

    private function getExpiration($entry, $status)
    {
        if (! $this->isDated()) {
            return;
        }

        // Past date behavior is private (eg. events that disappear after they happen)
        if ($status === 'published' && $this->collection()->pastDateBehavior() === 'private') {
            return $entry->date()->timestamp;
        }

        // When future date behavior is private (eg. scheduled blog posts)
        if ($status === 'scheduled' && $this->collection()->futureDateBehavior() === 'private') {
            return $entry->date()->timestamp;
        }
    }

    public function load()
    {
        if ($this->loaded) {
            return $this;
        }

        parent::load();

        $this->items = collect($this->items)->map(function ($value, $id) {
            [$status, $expiration] = explode('::', $value);

            if ($newStatus = $this->updateItemIfNecessary($expiration, $id)) {
                $status = $newStatus;
            }

            return $status;
        })->all();

        return $this;
    }

    public function update()
    {
        parent::update();

        $this->recentlyUpdated = true;

        return $this;
    }

    private function collection()
    {
        return $this->store->collection();
    }

    private function isDated()
    {
        return $this->collection()->dated();
    }

    private function updateItemIfNecessary($expiration, $id)
    {
        if (! $this->isDated() || $this->recentlyUpdated || ! $expiration) {
            return;
        }

        if (Carbon::createFromTimestamp($expiration)->isPast()) {
            $this->updateItem($entry = Entry::find($id));

            return $entry->status();
        }
    }

    public function items()
    {
        return parent::items()->map(fn ($item) => explode('::', $item)[0]);
    }
}
