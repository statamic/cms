<?php

namespace Statamic\API\Endpoint;

use Statamic\API\Config;
use Statamic\Search\Index;

class Search
{
    /**
     * Search instance
     *
     * @return \Statamic\Search\Search
     */
    private function search()
    {
        return app('Statamic\Search\Search');
    }

    /**
     * Perform a search and get the results
     *
     * @param  string $query String to search
     * @param  array|null $fields Fields to search in, or null to search all fields
     * @return array
     */
    public function get($query, $fields = null)
    {
        return self::search()->search($query, $fields);
    }

    /**
     * Get a search index
     *
     * @param  string $index Name of the index
     * @return Index
     */
    public function in($index)
    {
        return self::search()->index($index);
    }

    /**
     * Update a search index
     *
     * @param  string $index Name of the index
     * @return void
     */
    public function update($index = null)
    {
        try {
            return self::search()->update($index);
        } catch (\Exception $e) {
            \Log::error('Error updating the search index.');
            \Log::error($e);
        }
    }

    /**
     * Insert a value into the index
     *
     * @param mixed $id
     * @param mixed $value
     * @return mixed
     */
    public function insert($id, $value)
    {
        try {
            return self::search()->insert($id, $value);
        } catch (\Exception $e) {
            \Log::error("Error inserting [$id] into search index.");
            \Log::error($e);
        }
    }

    /**
     * Delete a value from the index
     *
     * @param mixed $id
     */
    public function delete($id)
    {
        try {
            return self::search()->delete($id);
        } catch (\Exception $e) {
            \Log::error("Error deleting [$id] from search index.");
        }
    }

    public function indexExists($index)
    {
        return self::search()->index($index)->exists();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function indexes()
    {
        $indexes = collect(['default' => [
            'driver' => Config::get('search.driver'),
            'fields' => Config::get('search.searchable')
        ]]);

        $collections = collect(Collection::all())->map(function ($collection) {
            if ($fields = $collection->get('searchable')) {
                $driver = $collection->get('search_driver');
                return compact('driver', 'fields');
            }
        })->filter()->keyByWithKey(function ($item, $key) {
            return 'collections/'.$key;
        });

        return $indexes->merge($collections);
    }
}
