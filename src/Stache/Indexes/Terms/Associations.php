<?php

namespace Statamic\Stache\Indexes\Terms;

use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Stache\Indexes\Index;
use Statamic\Support\Str;

class Associations extends Index
{
    private ?array $grouped = null;
    private ?array $groupedFrom = null;

    /**
     * What each entry has in the taxonomy's field is already in its collection's
     * index for that field, so the entries themselves don't need to be loaded.
     */
    public function getItems()
    {
        return Taxonomy::findByHandle($handle = $this->store->childKey())
            ->collections()
            ->flatMap(function ($collection) use ($handle) {
                $store = Stache::store('entries')->store($collection->handle());
                $sites = $store->index('site');

                return $store->index($handle)->items()
                    ->reject(fn ($values) => $values == null)
                    ->flatMap(function ($values, $id) use ($collection, $sites) {
                        return collect($values)->map(fn ($value) => [
                            'value' => $value,
                            'slug' => Str::slug($value),
                            'entry' => (string) $id,
                            'collection' => $collection->handle(),
                            'site' => $sites->get($id),
                        ]);
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
            // Associations that are loaded again (between queued jobs, say) come back
            // as a different array with the same contents. Holding on to that one
            // means the next comparison is of an array with itself, which is instant,
            // rather than of every association in it.
            $this->groupedFrom = $this->items;

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
