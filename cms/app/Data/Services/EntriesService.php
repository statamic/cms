<?php

namespace Statamic\Data\Services;

use Statamic\API\Helper;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Data\Entries\EntryCollection;
use Statamic\Stache\Repository;

class EntriesService extends AbstractService
{
    /**
     * The repo key
     *
     * @var string
     */
    protected $repo = 'entries';

    /**
     * Get an entry by ID
     *
     * @param string $id
     * @return Entry
     */
    public function id($id)
    {
        // If the provided ID doesn't exist, return nothing.
        if (! $repo = $this->repo()->getRepoById($id)) {
            return null;
        }

        return $repo->getItem($id);
    }

    /**
     * Check if an entry exists by ID
     *
     * @param string $id
     * @return bool
     */
    public function exists($id)
    {
        return ! $this->repo()->repos()->map(function (Repository $repo) use ($id) {
            return $repo->getIds()->has($id);
        })->filter()->isEmpty();
    }

    /**
     * Get an entry by slug
     *
     * @param string      $slug
     * @param string|null $collection  Optionally restrict to a collection
     * @return Entry
     */
    public function slug($slug, $collection = null)
    {
        $items = ($collection)
            ? $this->collection($collection)
            : $this->repo()->getItems();

        return $items->first(function ($id, $entry) use ($slug, $collection) {
            return $entry->slug() === $slug;
        });
    }

    /**
     * Check if an entry exists with a given slug
     *
     * @param string $slug
     * @param string|null $collection
     * @return bool
     */
    public function slugExists($slug, $collection = null)
    {
        return $this->slug($slug, $collection) !== null;
    }

    /**
     * Get an entry by URI
     *
     * @param string $uri
     * @return Entry
     */
    public function uri($uri)
    {
        $id = $this->repo()->repos()->map(function (Repository $repo) use ($uri) {
            return $repo->getIdByUri($uri);
        })->filter()->first();

        if (is_null($id)) {
            return;
        }

        return $this->id($id);
    }

    /**
     * Get all entries
     *
     * @return EntryCollection
     */
    public function all()
    {
        return collect_entries($this->repo()->repos()->flatMap(function ($repo) {
            return $repo->getItems();
        }));
    }

    /**
     * Get all the entries in a collection
     *
     * @param string $collection
     * @return EntryCollection
     */
    public function collection($collection)
    {
        return $this->collections(Helper::ensureArray($collection));
    }

    /**
     * Get all the entries in multiple collections
     *
     * @param array $collections
     * @return EntryCollection
     */
    public function collections($collections)
    {
        $entries = collect_entries();

        foreach ($collections as $collection) {
            $entries = $entries->merge(
                $this->repo()->repo($collection)->getItems()
            );
        }

        return $entries;
    }

    /**
     * Get the number of entries in a collection
     *
     * @param string $collection
     * @return int
     */
    public function countCollection($collection)
    {
        return $this->repo()->repo($collection)->getIds()->count();
    }
}