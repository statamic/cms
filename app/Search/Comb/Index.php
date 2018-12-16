<?php

namespace Statamic\Search\Comb;

use Statamic\Search\Documents;
use Illuminate\Filesystem\Filesystem;
use Statamic\Search\Index as BaseIndex;
use Statamic\Search\IndexNotFoundException;
use Statamic\Search\Comb\Exceptions\BadData;
use Statamic\Search\Comb\Exceptions\NoResultsFound;
use Statamic\Search\Comb\Exceptions\NotEnoughCharacters;

class Index extends BaseIndex
{
    protected $files;

    public function __construct(Filesystem $files, $name, array $config)
    {
        $this->files = $files;

        parent::__construct($name, $config);
    }

    public function search($query)
    {
        return (new Query($this))->query($query);
    }

    public function lookup($query)
    {
        $data = $this->data()->map(function ($item, $id) {
            return $item + ['id' => $id];
        })->values();

        $comb = new Comb($data, $this->settings());

        try {
            $results = $comb->lookUp($query)['data'];
        } catch (NoResultsFound | NotEnoughCharacters | BadData $e) {
            return collect();
        }

        return collect($results)->map(function ($result) {
            $data = $result['data'];
            $data['search_score'] = $result['score'];
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
            'include_properties' => null,
            'stop_words' => ['the', 'a', 'an'],
            'include_properties' => $this->config['fields'] ?? ['title']
        ], $this->config);
    }

    public function raw()
    {
        if (! $this->exists()) {
            throw new IndexNotFoundException;
        }

        return $this->files->get($this->path());
    }

    public function exists()
    {
        return $this->files->exists($this->path());
    }

    public function path()
    {
        return sprintf('%s/%s.json', $this->config['path'], $this->name);
    }

    protected function insertDocuments(Documents $documents)
    {
        try {
            $data = $this->data();
        } catch (IndexNotFoundException $e) {
            $data = collect();
        }

        $this->files->put($this->path(), $documents->union($data)->toJson());
    }

    public function deleteIndex()
    {
        $this->files->delete($this->path());
    }
}
