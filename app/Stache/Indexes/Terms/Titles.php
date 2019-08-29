<?php

namespace Statamic\Stache\Indexes\Terms;

use Statamic\API\Str;
use Statamic\API\Taxonomy;
use Statamic\API\Collection;
use Statamic\Stache\Indexes\Index;

class Titles extends Index
{
    public function getItems()
    {
        return $this
            ->getItemsFromAssociations()
            ->merge($this->getItemsFromFiles())
            ->all();
    }

    protected function getItemsFromFiles()
    {
        return $this->store->getItemsFromFiles()->map(function ($term) {
            return $term->title();
        });
    }

    protected function getItemsFromAssociations()
    {
        $taxonomy = $this->store->childKey();

        $collections = Taxonomy::findByHandle($taxonomy)->collections();

        // Get all the entries that have a value for this taxonomy.
        $entries = $collections->flatMap(function ($collection) use ($taxonomy) {
            return $collection->queryEntries()->where($taxonomy, '<>', null)->get();
        });

        // Get a slug to title mapping of terms across all the entries.
        return $entries->mapWithKeys(function ($entry) use ($taxonomy) {
            return collect($entry->value($taxonomy))->mapWithKeys(function ($term) {
                return [Str::slug($term) => $term];
            });
        });
    }

    protected function getItemValue($item)
    {
        return $item->title();
    }
}
