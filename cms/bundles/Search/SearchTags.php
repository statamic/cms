<?php

namespace Statamic\Addons\Search;

use Statamic\API\Config;
use Statamic\API\Str;
use Statamic\API\Search;
use Statamic\API\Content;
use Statamic\Addons\Collection\CollectionTags;
use Statamic\Search\IndexNotFoundException;

class SearchTags extends CollectionTags
{
    /**
     * The search query
     *
     * @var string
     */
    private $query;

    /**
     * The {{ search }} tag. An alias of search:results
     *
     * @return string
     */
    public function index()
    {
        return $this->results();
    }

    /**
     * The {{ search:results }} tag
     *
     * @return string
     */
    public function results()
    {
        try {
            $this->collection = $this->buildSearchCollection();
        } catch (IndexNotFoundException $e) {
            \Log::debug($e->getMessage());
            return $this->parseNoResults();
        }

        // By default, each item from the search index will be replaced with the corresponding
        // data object. This has extra overhead, so if the user only needs to display data
        // already in the index, then this can be disabled for a speed boost.
        if ($this->getBool('supplement_data', true)) {
            $this->convertSearchResultsToContent();

            // Convert taxonomy fields to actual taxonomy terms.
            // This will allow taxonomy term data to be available in the template without additional tags.
            // If terms are not needed, there's a slight performance benefit in disabling this.
            if ($this->getBool('supplement_taxonomies', true)) {
                $this->collection = $this->collection->supplementTaxonomies();
            }

            $this->filter(false);
        }

        $this->limit();

        if ($this->collection->isEmpty()) {
            return $this->parseNoResults();
        }

        return $this->output();
    }

    protected function getSortOrder()
    {
        return $this->get('sort', 'search_score:desc');
    }

    /**
     * Perform a search and generate a collection
     *
     * @return \Illuminate\Support\Collection
     */
    private function buildSearchCollection()
    {
        $index = ($collection = $this->get('collection'))
            ? 'collections/' . $collection
            : Config::get('search.default_index');

        $query = request()->query($this->get('param', 'q'));

        return Search::in($index)->search($query, $this->getList('fields'));
    }

    private function convertSearchResultsToContent()
    {
        $collection = $this->collection->map(function ($result) {
            if (! $content = Content::find($result['id'])) {
                return null;
            }

            return $content;
        })->filter();

        $this->collection = collect_content($collection);
    }
}
