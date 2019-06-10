<?php

namespace Statamic\API\Endpoint;

use Statamic\Events\Data\CollectionSaved;
use Statamic\Events\Data\CollectionDeleted;
use Statamic\Contracts\Data\Repositories\CollectionRepository;
use Statamic\Contracts\Data\Entries\Collection as CollectionContract;

class Collection
{
    /**
     * Get all collections
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return $this->repo()->all()->sortBy(function ($collection) {
            return $collection->title();
        });
    }

    /**
     * Get the handles of all collections
     *
     * @return array
     */
    public function handles()
    {
        return self::all()->keys()->all();
    }

    /**
     * Get a collection by handle
     *
     * @param string $handle
     * @return \Statamic\Contracts\Data\Entries\Collection
     */
    public function findByHandle($handle)
    {
        return $this->repo()->findByHandle($handle);
    }

    public function findByMount($mount)
    {
        return $this->repo()->findByMount($mount);
    }

    public function updateEntryUris($collection)
    {
        return $this->repo()->updateEntryUris($collection);
    }

    /**
     * Check if a collection exists by its handle
     *
     * @param string $handle
     * @return bool
     */
    public function handleExists($handle)
    {
        return self::findByHandle($handle) !== null;
    }

    public function make($handle = null)
    {
        $collection = app(CollectionContract::class);

        if ($handle) {
            $collection->handle($handle);
        }

        return $collection;
    }

    public function create($handle)
    {
        // TODO: Remove.
        return $this->make($handle);
    }

    public function save(CollectionContract $collection)
    {
        $this->repo()->save($collection);

        CollectionSaved::dispatch($collection);
    }

    public function delete(CollectionContract $collection)
    {
        $this->repo()->delete($collection);

        CollectionDeleted::dispatch($collection);
    }

    protected function repo(): CollectionRepository
    {
        return app(CollectionRepository::class);
    }
}
