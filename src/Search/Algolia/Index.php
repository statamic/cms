<?php

namespace Statamic\Search\Algolia;

use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;
use Algolia\AlgoliaSearch\SearchClient;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Arr;
use Statamic\Search\Documents;
use Statamic\Search\Index as BaseIndex;
use Statamic\Search\IndexNotFoundException;
use Statamic\Support\Str;

class Index extends BaseIndex
{
    protected $client;

    public function __construct(SearchClient $client, $name, $config, $locale)
    {
        $this->client = $client;

        parent::__construct($name, $config, $locale);
    }

    public function search($query)
    {
        return (new Query($this))->query($query);
    }

    protected function insertDocuments(Documents $documents)
    {
        $documents = $documents->map(function ($item, $id) {
            $item['objectID'] = $id;

            return $item;
        })->values();

        try {
            $this->getIndex()->saveObjects($documents);
        } catch (ConnectException $e) {
            throw new \Exception('Error connecting to Algolia. Check your API credentials.', 0, $e);
        }
    }

    public function delete($document)
    {
        $this->getIndex()->deleteObject($document->getSearchReference());
    }

    public function deleteIndex()
    {
        $this->getIndex()->delete();
    }

    public function update()
    {
        $this->getIndex()->clearObjects();

        $this->insertMultiple($this->searchables()->all());

        return $this;
    }

    public function getIndex()
    {
        return $this->client->initIndex($this->name);
    }

    public function searchUsingApi($query, $fields = null)
    {
        $arguments = [];

        if ($fields) {
            $arguments['restrictSearchableAttributes'] = implode(',', Arr::wrap($fields));
        }

        try {
            $response = $this->getIndex()->search($query, $arguments);
        } catch (AlgoliaException $e) {
            $this->handleAlgoliaException($e);
        }

        return collect($response['hits'])->map(function ($hit) {
            $hit['reference'] = $hit['objectID'];

            return $hit;
        });
    }

    public function exists()
    {
        return null !== collect($this->client->listIndices()['items'])->first(function ($index) {
            return $index['name'] == $this->name;
        });
    }

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

        throw $e;
    }
}
