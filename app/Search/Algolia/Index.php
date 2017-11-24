<?php

namespace Statamic\Search\Algolia;

use Statamic\API\Str;
use AlgoliaSearch\Client;
use Statamic\Search\ItemResolver;
use AlgoliaSearch\AlgoliaException;
use Statamic\Events\SearchQueryPerformed;
use Statamic\Search\Index as AbstractIndex;
use Statamic\Search\IndexNotFoundException;

class Index extends AbstractIndex
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var \AlgoliaSearch\Index
     */
    protected $index;

    /**
     * @param ItemResolver $itemResolver
     * @param Client $client
     */
    public function __construct(ItemResolver $itemResolver, Client $client)
    {
        parent::__construct($itemResolver);

        $this->client = $client;
    }

    /**
     * @inheritdoc
     */
    public function insert($id, $fields)
    {
        $fields['objectID'] = $id;

        $this->getIndex()->saveObject($fields);
    }

    /**
     * @inheritdoc
     */
    public function insertMultiple($documents)
    {
        $this->getIndex()->deleteObjects(array_keys($documents));

        $objects = collect($documents)->map(function ($item, $id) {
             $item['objectID'] = $id;
             return $item;
        })->values();

        $this->getIndex()->saveObjects($objects);
    }

    /**
     * @inheritdoc
     */
    public function delete($id)
    {
        $this->getIndex()->deleteObject($id);
    }

    /**
     * @inheritdoc
     */
    public function search($query, $fields = null)
    {
        event(new SearchQueryPerformed($query));

        $arguments = [
            'restrictSearchableAttributes' => implode(',', is_array($fields) ? $fields : array($fields))
        ];

        try {
            $response = $this->getIndex()->search($query, $arguments);
        } catch (AlgoliaException $e) {
            $this->handleAlgoliaException($e);
            \Log::error($e);
            return [];
        }

        return collect($response['hits'])->map(function ($hit) {
            $hit['id'] = $hit['objectID'];
            return $hit;
        });
    }

    /**
     * @inheritdoc
     */
    public function deleteIndex()
    {
        $this->getIndex()->clearIndex();
    }

    /**
     * Get the Algolia index.
     *
     * @return \AlgoliaSearch\Index
     */
    public function getIndex()
    {
        if (! $this->index) {
            $name = str_replace('/', '_', $this->name);
            $this->index = $this->client->initIndex($name);
        }

        return $this->index;
    }

    /**
     * Throws a more user friendly exception.
     *
     * @param AlgoliaException $e
     * @throws \Exception
     */
    private function handleAlgoliaException($e)
    {
        if (Str::contains($e->getMessage(), "Index {$this->name} does not exist")) {
            throw new IndexNotFoundException("Index [{$this->name}] does not exist.");
        }

        if (preg_match('/attribute (.*) is not in searchableAttributes/', $e->getMessage(), $matches)) {
            throw new \Exception(
                "Field [{$matches[1]}] does not exist in this index's searchableAttributes list."
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function exists()
    {
        return null !== collect($this->client->listIndexes()['items'])->first(function ($i, $index) {
            return $index['name'] == str_replace('/', '_', $this->name);
        });
    }
}
