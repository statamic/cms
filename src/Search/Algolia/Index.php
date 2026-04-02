<?php

namespace Statamic\Search\Algolia;

use Algolia\AlgoliaSearch\Api\SearchClient;
use Algolia\AlgoliaSearch\Exceptions\AlgoliaException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Arr;
use Statamic\Search\Documents;
use Statamic\Search\Index as BaseIndex;
use Statamic\Search\IndexNotFoundException;
use Statamic\Search\Result;
use Statamic\Support\Str;

class Index extends BaseIndex
{
    private SearchClient $client;
    private bool $settingsInitialized = false;

    public function __construct(SearchClient $client, $name, $config, $locale)
    {
        $this->client = $client;

        parent::__construct($name, $config, $locale);
    }

    protected function client(): SearchClient
    {
        if (! $this->settingsInitialized && isset($this->config['settings']) && ! $this->exists()) {
            $this->client->setSettings($this->name, $this->config['settings']);
            $this->settingsInitialized = true;
        }

        return $this->client;
    }

    public function search($query)
    {
        return (new Query($this))->query($query);
    }

    public function insertDocuments(Documents $documents)
    {
        $documents = $documents->map(function ($item, $id) {
            $item['objectID'] = $id;

            return $item;
        })->values();

        try {
            $this->client()->saveObjects($this->name, $documents->all());
        } catch (ConnectException $e) {
            throw new \Exception('Error connecting to Algolia. Check your API credentials.', 0, $e);
        }
    }

    public function delete($document)
    {
        $this->client()->deleteObject($this->name, $document->getSearchReference());
    }

    public function deleteIndex()
    {
        $this->client()->deleteIndex($this->name);
    }

    public function update()
    {
        $this->client()->clearObjects($this->name);

        if (isset($this->config['settings'])) {
            $this->client()->setSettings($this->name, $this->config['settings']);
        }

        $this->searchables()->lazy()->each(fn ($searchables) => $this->insertMultiple($searchables));

        return $this;
    }

    public function searchUsingApi($query, $fields = null)
    {
        $arguments = ['query' => $query];

        if ($fields) {
            $arguments['restrictSearchableAttributes'] = implode(',', Arr::wrap($fields));
        }

        try {
            $response = $this->client()->searchSingleIndex($this->name, $arguments);
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
        return collect($this->client->listIndices()['items'])->first(function ($index) {
            return $index['name'] == $this->name;
        }) !== null;
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

    public function extraAugmentedResultData(Result $result)
    {
        return [
            'search_highlights' => $result->getRawResult()['_highlightResult'] ?? null,
            'search_snippets' => $result->getRawResult()['_snippetResult'] ?? null,
        ];
    }
}
