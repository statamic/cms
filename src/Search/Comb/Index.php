<?php

namespace Statamic\Search\Comb;

use Statamic\Facades\File;
use Statamic\Search\Comb\Exceptions\NoQuery;
use Statamic\Search\Comb\Exceptions\NoResultsFound;
use Statamic\Search\Comb\Exceptions\NotEnoughCharacters;
use Statamic\Search\Documents;
use Statamic\Search\Index as BaseIndex;
use Statamic\Search\IndexNotFoundException;
use Statamic\Search\Result;

class Index extends BaseIndex
{
    public function search($query)
    {
        return (new Query($this))->query($query);
    }

    public function lookup($query)
    {
        $data = $this->data()->map(function ($item, $reference) {
            return $item + ['reference' => $reference];
        })->values()->toArray();

        $comb = new Comb($data, $this->settings());

        try {
            $results = $comb->lookUp($query)['data'];
        } catch (NoResultsFound|NotEnoughCharacters|NoQuery $e) {
            return collect();
        }

        return collect($results)->map(function ($result) {
            $data = $result['data'];
            $data['search_score'] = $result['score'];
            $data['search_snippets'] = $result['snippets'];

            return array_except($data, '_category');
        });
    }

    protected function data()
    {
        return collect(json_decode($this->raw(), true));
    }

    protected function settings()
    {
        return array_merge([
            'match_weights' => null,
            'min_characters' => null,
            'min_word_characters' => null,
            'score_threshold' => null,
            'property_weights' => null,
            'query_mode' => null,
            'use_stemming' => false,
            'use_alternates' => false,
            'include_full_query' => null,
            'enable_too_many_results' => null,
            'sort_by_score' => null,
            'exclude_properties' => null,
            'stop_words' => ['the', 'a', 'an'],
            'include_properties' => $this->config['fields'] ?? ['title'],
        ], $this->config);
    }

    public function raw()
    {
        if (! $this->exists()) {
            throw new IndexNotFoundException;
        }

        return File::get($this->path());
    }

    public function exists()
    {
        return File::exists($this->path());
    }

    public function path()
    {
        return sprintf('%s/%s.json', $this->config['path'], $this->name);
    }

    public function delete($document)
    {
        try {
            $data = $this->data();
        } catch (IndexNotFoundException $e) {
            return;
        }

        $data->forget($document->getSearchReference());

        $this->save($data);
    }

    protected function insertDocuments(Documents $documents)
    {
        try {
            $data = $this->data();
        } catch (IndexNotFoundException $e) {
            $data = collect();
        }

        $this->save($documents->union($data));
    }

    public function deleteIndex()
    {
        File::delete($this->path());
    }

    protected function save($documents)
    {
        File::put($this->path(), $documents->toJson());
    }

    public function extraAugmentedResultData(Result $result)
    {
        return [
            'search_snippets' => $result->getRawResult()['search_snippets'],
        ];
    }
}
