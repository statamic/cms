<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Config;
use Statamic\API\Folder;
use Statamic\API\Search;
use Statamic\API\Content;
use Illuminate\Http\Request;
use Statamic\Search\IndexNotFoundException;

class SearchController extends CpController
{
    /**
     * The view for /cp/search
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return 'Todo. For now, go to /cp/search/perform?query=your+term';
    }

    /**
     * Update the search index
     */
    public function update()
    {
        Search::update();

        return 'Index updated.';
    }

    /**
     * Search for a term
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function search(Request $request)
    {
        if (! $this->isIndexed()) {
            Search::update();
        }

        $query = $request->query('q');

        // The search update would have been triggered if the index didn't exist, but it's possible that it's not
        // ready by the time the search is performed, resulting in an exception. In this case, we'll gracefully
        // fall back to empty results. Typing another character or two will eventually yield results anyway.
        try {
            $results = Search::get($query);
        } catch (IndexNotFoundException $e) {
            return [];
        }

        foreach ($results as $key => $result) {
            $id = $result['id'];
            $content = Content::find($id)->toArray();
            $results[$key] = $content;
        }

        return $results;
    }

    /**
     * Determine if an index has already been created.
     *
     * @return boolean
     */
    private function isIndexed()
    {
        return Search::indexExists(Config::get('search.default_index'));
    }
}
