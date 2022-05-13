<?php

namespace Statamic\Stache\Indexes;

use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Entry;

class Status extends Value
{
    public function load()
    {
        if ($this->loaded) {
            return $this;
        }

        $this->loaded = true;

        debugbar()->addMessage("Loading index: {$this->store->key()}/{$this->name}", 'stache');

        $this->items = Cache::get($this->cacheKey());

        if ($this->items === null) {
            $this->update();
        }

        $this->expirableItems()->each(function ($status, $id) {
            $this->updateItem(Entry::find($id));
        });

        $this->store->cacheIndexUsage($this);

        return $this;
    }

    protected function expirableItems()
    {
        return collect($this->items)->filter(function ($value, $id) {
            return $value === 'scheduled' && $this->store->index('date')->get($id)->isPast();
        });
    }
}
