<?php

namespace Statamic\Search;

class Search
{
    private $defaultDriver;
    private $defaultIndexName;
    private $indexes;

    /**
     * Create a new Search instance
     *
     * @param array $config
     * @param \Illuminate\Support\Collection $indexes
     */
    public function __construct(array $config, $indexes = null)
    {
        $this->defaultDriver = $config['default_driver'];
        $this->defaultIndexName = $config['default_index'];
        $this->indexes = $indexes;
    }

    /**
     * Perform a search
     *
     * @param  string $query String to search
     * @param  array|null $fields Fields to search in, or null to search all fields
     * @return array
     */
    public function search($query, $fields = null)
    {
        return $this->index()->search($query, $fields);
    }

    /**
     * Get a search index
     *
     * @param  string $index Name of the index
     * @return Index
     */
    public function index($index = null)
    {
        if (! $index || $index === $this->defaultIndexName) {
            return Index::make($this->defaultIndexName, $this->defaultDriver);
        }

        return Index::make(
            $index,
            data_get($this->indexes, $index.'.driver')
        );
    }

    /**
     * Update a search index
     *
     * @param  string $index Name of the index
     * @return void
     */
    public function update($index = null)
    {
        $this->index($index)->update();
    }

    /**
     * Provide convenient access to methods on the default index
     *
     * @param  string $method
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        return call_user_func_array(array($this->index(), $method), $arguments);
    }
}
