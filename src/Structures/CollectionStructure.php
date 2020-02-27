<?php

namespace Statamic\Structures;

class CollectionStructure extends Structure
{
    public function handle($handle = null)
    {
        if (func_num_args() === 1) {
            throw new \LogicException('Handle cannot be set.');
        }

        if ($collection = $this->collection()) {
            return 'collection::' . $collection->handle();
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
        return is_array($route = $this->collection->route())
            ? ($route[$site] ?? null)
            : $route;
    }

    public function validateTree(array $tree): void
    {
        parent::validateTree($tree);

        $entryIds = $this->getEntryIdsFromTree($tree);

        if ($entryId = $entryIds->duplicates()->first()) {
            throw new \Exception("Duplicate entry [{$entryId}] in [{$this->collection->handle()}] collection's structure.");
        }
    }

    protected function getEntryIdsFromTree($tree)
    {
        return collect($tree)
            ->map(function ($item) {
                return [
                    'entry' => $item['entry'] ?? null,
                    'children' => isset($item['children']) ? $this->getEntryIdsFromTree($item['children']) : null
                ];
            })
            ->flatten()
            ->filter();
    }
}
