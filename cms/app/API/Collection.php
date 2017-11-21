<?php

namespace Statamic\API;

use Statamic\Data\Services\CollectionsService;

class Collection
{
    /**
     * Get all collections
     *
     * @return \Illuminate\Support\Collection
     */
    public static function all()
    {
        return app(CollectionsService::class)->all()->sortBy(function ($collection) {
            return $collection->title();
        });
    }

    /**
     * Get the handles of all collections
     *
     * @return array
     */
    public static function handles()
    {
        return self::all()->keys()->all();
    }

    /**
     * Get a collection by handle
     *
     * @param string $handle
     * @return \Statamic\Contracts\Data\Entries\Collection
     */
    public static function whereHandle($handle)
    {
        return app(CollectionsService::class)->handle($handle);
    }

    /**
     * Check if a collection exists by its handle
     *
     * @param string $handle
     * @return bool
     */
    public static function handleExists($handle)
    {
        return self::whereHandle($handle) !== null;
    }

    /**
     * Create a collection
     *
     * @param string $handle
     * @return \Statamic\Contracts\Data\Entries\Collection
     */
    public static function create($handle)
    {
        /** @var \Statamic\Contracts\Data\Entries\Collection $collection */
        $collection = app('Statamic\Contracts\Data\Entries\Collection');

        $collection->path($handle);

        return $collection;
    }
}