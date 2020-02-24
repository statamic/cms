<?php

namespace Statamic\Stache\Indexes;

use Statamic\Support\Str;
use Statamic\Facades\Structure;

class StructureUris extends Index
{
    public function getItems()
    {
        return Structure::all()->filter(function ($structure) {
            return $structure->collection();
        })->flatMap(function ($structure) {
            return $this->structureUris($structure);
        })->all();
    }

    protected function structureUris($structure)
    {
        return $structure->trees()->flatMap(function ($tree) {
            return $this->referencedPageUris($tree);
        });
    }

    protected function referencedPageUris($tree)
    {
        $site = $tree->locale();
        $collection = $tree->structure()->collection()->handle();

        return $tree->flattenedPages()->filter(function ($page) {
            return $page->reference() && $page->referenceExists();
        })->mapWithKeys(function ($page) use ($collection, $site) {
            $key = $site . '::' . $page->uri();
            $value = $collection . '::' . $page->reference();
            return [$key => $value];
        });
    }

    public function updateItem($item)
    {
        if (! $item->isCollectionBased()) {
            return;
        }

        $this->load();

        // Remove this structure's values, then add back fresh versions.
        $this->items = collect($this->items)
            ->reject(function ($key) use ($item) {
                return Str::startsWith($key, $item->handle() . '::');
            })
            ->merge($this->structureUris($item))
            ->all();

        $this->cache();
    }
}
