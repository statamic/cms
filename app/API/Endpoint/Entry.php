<?php

namespace Statamic\API\Endpoint;

use Statamic\Contracts\Data\Repositories\StructureRepository;

class Entry
{
    /**
     * The service for interacting with entries
     *
     * @return EntriesService
     */
    private function service()
    {
        return app(\Statamic\Contracts\Data\Repositories\EntryRepository::class);
    }

    /**
     * Find an entry by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Data\Entries\Entry
     */
    public function find($id)
    {
        return self::service()->find($id);
    }

    /**
     * Get all entries
     *
     * @return \Statamic\Data\Entries\EntryCollection
     */
    public function all()
    {
        return self::service()->all();
    }

    /**
     * Get entries in one or more collections.
     *
     * @param string|array $collection  Either a collection handle, or an array of collection handles.
     * @return \Statamic\Data\Entries\EntryCollection
     */
    public function whereCollection($collection)
    {
        return is_array($collection)
            ? $this->service()->whereInCollection($collection)
            : $this->service()->whereCollection($collection);
    }

    /**
     * Get an entry by slug and collection
     *
     * @param string $slug
     * @param string $collection
     * @return \Statamic\Contracts\Data\Entries\Entry
     */
    public function findBySlug($slug, $collection)
    {
        return $this->service()->findBySlug($slug, $collection);
    }

    /**
     * Get an entry by URI
     *
     * @param string $uri
     * @return \Statamic\Contracts\Data\Entries\Entry
     */
    public function whereUri($uri)
    {
        return app(StructureRepository::class)->findEntryByUri($uri)
            ?? $this->service()->findByUri($uri);
    }

    /**
     * Check if an entry exists
     *
     * @param string $id
     * @return bool
     */
    public function exists($id)
    {
        return self::service()->exists($id);
    }

    /**
     * Check if an entry exists by slug
     *
     * @param string $slug
     * @param string $collection
     * @return bool
     */
    public function slugExists($slug, $collection)
    {
        return self::service()->slugExists($slug, $collection);
    }

    /**
     * Get the number of entries in a given collection
     *
     * @param string $collection
     * @return int
     */
    public function countWhereCollection($collection)
    {
        return self::service()->countCollection($collection);
    }

    /**
     * Create an entry
     *
     * @param string $slug
     * @return \Statamic\Contracts\Data\Entries\EntryFactory
     */
    public function create($slug)
    {
        return app('Statamic\Contracts\Data\Entries\EntryFactory')->create($slug);
    }

    /**
     * Get an entry from a collection, by its slug
     *
     * @param string       $collection
     * @param string       $slug
     * @return \Statamic\Contracts\Data\Entries\Entry
     * @deprecated since 2.1
     */
    public function getFromCollection($collection, $slug)
    {
        \Log::notice('Entry::getFromCollection() is deprecated. Use Entry::whereSlug()');

        return self::whereSlug($slug, $collection);
    }

    /**
     * Get an entry by UUID
     *
     * @param string $uuid
     * @return \Statamic\Contracts\Data\Entries\Entry
     * @deprecated since 2.11
     */
    public function getByUuid($uuid)
    {
        \Log::notice('Entry::getByUuid() is deprecated. Use Entry::find()');

        return self::find($uuid);
    }
}
