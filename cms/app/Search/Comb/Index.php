<?php

namespace Statamic\Search\Comb;

use Statamic\API\File;
use Statamic\Events\SearchQueryPerformed;
use Statamic\Search\IndexNotFoundException;
use Statamic\Search\Index as AbstractIndex;
use Statamic\Search\Comb\Exceptions\BadData;
use Statamic\Search\Comb\Exceptions\NoResultsFound;
use Statamic\Search\Comb\Exceptions\NotEnoughCharacters;

class Index extends AbstractIndex
{
    /**
     * @inheritdoc
     */
    public function insert($id, $fields)
    {
        try {
            $data = $this->getIndexData();
        } catch (IndexNotFoundException $e) {
            $data = [];
        }

        $data[$id] = $fields;

        File::put($this->getPath(), json_encode($data));
    }

    /**
     * @inheritdoc
     */
    public function insertMultiple($documents)
    {
        try {
            $data = $this->getIndexData();
        } catch (IndexNotFoundException $e) {
            $data = [];
        }

        foreach ($documents as $id => $document) {
            $data[$id] = $document;
        }

        File::put($this->getPath(), json_encode($data));
    }

    /**
     * @inheritdoc
     */
    public function search($query, $fields = null)
    {
        event(new SearchQueryPerformed($query));

        $settings = array(
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
        );

        $settings['include_properties'] = $fields;

        $data = collect($this->getIndexData())->map(function ($item, $id) {
            $item['id'] = $id;
            return $item;
        })->values()->all();

        try {
            $comb = new \Statamic\Search\Comb\Comb($data, $settings);
            $results = $comb->lookUp($query)['data'];
        } catch (NoResultsFound $e) {
            return collect();
        } catch (NotEnoughCharacters $e) {
            return collect();
        } catch (BadData $e) {
            return collect();
        }

        return collect($results)->map(function ($result) {
            $data = $result['data'];
            $data['search_score'] = $result['score'];
            return $data;
        });
    }

    /**
     * @inheritdoc
     */
    public function delete($id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @inheritdoc
     */
    public function deleteIndex()
    {
        if ($this->exists()) {
            File::delete($this->getPath());
        }
    }

    /**
     * Get the data/contents from the index.
     *
     * @return array
     * @throws IndexNotFoundException
     */
    private function getIndexData()
    {
        if (! $this->exists()) {
            throw new IndexNotFoundException("Index [$this->name] does not exist.");
        }

        return json_decode(File::get($this->getPath()), true);
    }

    /**
     * @inheritdoc
     */
    public function exists()
    {
        return File::exists($this->getPath());
    }

    /**
     * Get the path to the index file.
     *
     * @return string
     */
    private function getPath()
    {
        return 'local/storage/search/' . $this->name . '.json';
    }
}
