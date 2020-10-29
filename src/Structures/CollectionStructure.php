<?php

namespace Statamic\Structures;

use Facades\Statamic\Contracts\Structures\TreeRepository;

class CollectionStructure extends Structure
{
    public function handle($handle = null)
    {
        if (func_num_args() === 1) {
            throw new \LogicException('Handle cannot be set.');
        }

        if ($collection = $this->collection()) {
            return 'collection::'.$collection->handle();
        }
    }

    public function title($title = null)
    {
        if (func_num_args() === 1) {
            throw new \LogicException('Title cannot be set.');
        }

        return $this->collection()->title();
    }

    public function collection($collection = null)
    {
        return $this
            ->fluentlyGetOrSet('collection')
            ->args(func_get_args());
    }

    public function entryUri($entry)
    {
        if (! $this->route($entry->locale())) {
            return null;
        }

        $page = $this->in($entry->locale())
            ->flattenedPages()
            ->keyBy->reference()
            ->get($entry->id());

        return optional($page)->uri();
    }

    public function collections($collections = null)
    {
        // return collect([$this->collection]);
    }

    public function route(string $site): ?string
    {
        return $this->collection->route($site);
    }

    public function newTreeInstance()
    {
        return new CollectionStructureTree;
    }

    public function validateTree(array $tree, string $locale): array
    {
        parent::validateTree($tree, $locale);

        $entryIds = $this->getEntryIdsFromTree($tree);

        if ($entryId = $entryIds->duplicates()->first()) {
            throw new \Exception("Duplicate entry [{$entryId}] in [{$this->collection->handle()}] collection's structure.");
        }

        $thisCollectionsEntries = $this->collection->queryEntries()
            ->where('site', $locale)
            ->get(['id', 'site'])
            ->map->id();

        $otherCollectionEntries = $entryIds->diff($thisCollectionsEntries);

        if ($otherCollectionEntries->isNotEmpty()) {
            $tree = $this->removeEntryReferencesFromTree($tree, $otherCollectionEntries);
        }

        $missingEntries = $thisCollectionsEntries->diff($entryIds)->map(function ($id) {
            return ['entry' => $id];
        })->values()->all();

        return array_merge($tree, $missingEntries);
    }

    protected function getEntryIdsFromTree($tree)
    {
        return collect($tree)
            ->map(function ($item) {
                return [
                    'entry' => $item['entry'] ?? null,
                    'children' => isset($item['children']) ? $this->getEntryIdsFromTree($item['children']) : null,
                ];
            })
            ->flatten()
            ->filter();
    }

    protected function removeEntryReferencesFromTree($tree, $entries)
    {
        return collect($tree)
            ->reject(function ($branch) use ($entries) {
                return $entries->contains($branch['entry']);
            })
            ->map(function ($branch) use ($entries) {
                if (isset($branch['children'])) {
                    $branch['children'] = $this->removeEntryReferencesFromTree($branch['children'], $entries);
                }

                return $branch;
            })
            ->all();
    }

    public function save()
    {
        $this->collection()->structure($this)->save();

        return true;
    }

    public function trees()
    {
        return $this->collection()->sites()->mapWithKeys(function ($site) {
            return [$site => $this->in($site)];
        });
    }

    public function in($site)
    {
        if (isset($this->trees[$site])) {
            return $this->trees[$site];
        }

        $name = 'collections/'.$this->collection()->handle();

        if ($this->collection()->sites()->count() > 1) {
            $name .= '/'.$site;
        }

        if ($tree = TreeRepository::find($name)) {
            $tree->locale($site)->structure($this);
        }

        if (! $tree && $this->existsIn($site)) {
            $tree = $this->makeTree($site);
        }

        $this->trees[$site] = $tree;

        return $tree;
    }

    public function existsIn($site)
    {
        return $this->collection()->sites()->contains($site);
    }
}
