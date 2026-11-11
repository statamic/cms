<?php

namespace Statamic\Stache\Indexes\Terms;

use Statamic\Facades\Taxonomy;
use Statamic\Stache\Indexes\Index;
use Statamic\Support\Str;

class Associations extends Index
{
    private ?array $grouped = null;
    private ?array $groupedFrom = null;

    public function getItems()
    {
        return Taxonomy::findByHandle($handle = $this->store->childKey())
            ->collections()
            ->flatMap(function ($collection) use ($handle) {
                return $collection->queryEntries()
                    ->where($handle, '<>', null)
                    ->get()
                    ->flatMap(function ($entry) use ($handle) {
                        return collect($entry->value($handle))
                            ->map(function ($value) use ($entry) {
                                return [
                                    'value' => $value,
                                    'slug' => Str::slug($value),
                                    'entry' => $entry->id(),
                                    'collection' => $entry->collectionHandle(),
                                    'site' => $entry->locale(),
                                ];
                            });
                    })->all();
            })->all();
    }

    /**
     * The associations for a term, found by its slug.
     */
    public function forSlug($slug): array
    {
        return $this->grouped()['slug'][(string) $slug] ?? [];
    }

    /**
     * The associations for any of the given term slugs, in the order they are stored.
     */
    public function forSlugs(array $slugs): array
    {
        $associations = [];

        foreach (array_unique(array_map('strval', $slugs)) as $slug) {
            $associations += $this->forSlug($slug);
        }

        ksort($associations);

        return $associations;
    }

    /**
     * The associations for a term, found by the value entries store for it.
     */
    public function forValue($value): array
    {
        return $this->grouped()['value'][(string) $value] ?? [];
    }

    /**
     * The slugs of the terms that entries in any of the given collections use.
     */
    public function slugsInCollections(array $collections): array
    {
        $slugs = [];

        foreach ($collections as $collection) {
            $slugs += $this->grouped()['collection'][(string) $collection] ?? [];
        }

        return array_map('strval', array_keys($slugs));
    }

    /**
     * Every query for a term's entries, or for the terms a collection uses,
     * would otherwise read through every association of the taxonomy. The
     * associations are grouped once and looked up after that. The grouping
     * is thrown away as soon as the associations are no longer the ones it
     * was made from, however they came to change.
     */
    private function grouped(): array
    {
        $this->load();

        if ($this->grouped !== null && $this->groupedFrom === $this->items) {
            return $this->grouped;
        }

        $grouped = ['slug' => [], 'value' => [], 'collection' => []];

        foreach ($this->items ?? [] as $i => $association) {
            $grouped['slug'][(string) $association['slug']][$i] = $association;
            $grouped['value'][(string) $association['value']][$i] = $association;
            $grouped['collection'][(string) $association['collection']][(string) $association['slug']] = true;
        }

        $this->groupedFrom = $this->items;

        return $this->grouped = $grouped;
    }

    public function forgetEntry($id)
    {
        $this->items = $this->items()->reject(function ($association) use ($id) {
            return $association['entry'] === $id;
        })->all();
    }

    public function updateItem($item)
    {
        //
    }
}
