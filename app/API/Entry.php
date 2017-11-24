<?php

namespace Statamic\API;

use Statamic\Data\Services\EntriesService;

class Entry
{
    /**
     * The service for interacting with entries
     *
     * @return EntriesService
     */
    private static function service()
    {
        return app(EntriesService::class);
    }

    /**
     * Find an entry by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Data\Entries\Entry
     */
    public static function find($id)
    {
        return self::service()->id($id);
    }

    /**
     * Get all entries
     *
     * @return \Statamic\Data\Entries\EntryCollection
     */
    public static function all()
    {
        return self::service()->all();
    }

    /**
     * Get entries in a collection
     *
     * @param string $collection
     * @return \Statamic\Data\Entries\EntryCollection
     */
    public static function whereCollection($collection)
    {
        return self::service()->collection($collection);
    }

    /**
     * Get an entry by slug and collection
     *
     * @param string $slug
     * @param string $collection
     * @return \Statamic\Contracts\Data\Entries\Entry
     */
    public static function whereSlug($slug, $collection)
    {
        return self::service()->slug($slug, $collection);
    }

    /**
     * Get an entry by URI
     *
     * @param string $uri
     * @return \Statamic\Contracts\Data\Entries\Entry
     */
    public static function whereUri($uri)
    {
        return self::service()->uri($uri);
    }

    /**
     * Check if an entry exists
     *
     * @param string $id
     * @return bool
     */
    public static function exists($id)
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
    public static function slugExists($slug, $collection)
    {
        return self::service()->slugExists($slug, $collection);
    }

    /**
     * Get the number of entries in a given collection
     *
     * @param string $collection
     * @return int
     */
    public static function countWhereCollection($collection)
    {
        return self::service()->countCollection($collection);
    }

    /**
     * Create an entry
     *
     * @param string $slug
     * @return \Statamic\Contracts\Data\Entries\EntryFactory
     */
    public static function create($slug)
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
    public static function getFromCollection($collection, $slug)
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
    public static function getByUuid($uuid)
    {
        \Log::notice('Entry::getByUuid() is deprecated. Use Entry::find()');

        return self::find($uuid);
    }
}
