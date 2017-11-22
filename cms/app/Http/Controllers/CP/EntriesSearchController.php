<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Str;
use Statamic\API\Entry;
use Statamic\API\Config;
use Statamic\API\Search;
use Statamic\API\Collection;
use Illuminate\Http\Request;

class EntriesSearchController extends CpController
{
    public function search(Request $request, $collection)
    {
        $query = $request->query('q');

        if (Search::indexExists('collections/'.$collection)) {
            $entries = $this->searchCollectionIndex($collection, $query);
        } elseif (Search::indexExists(Config::get('search.default_index'))) {
            $entries = $this->searchDefaultIndex($collection, $query);
        } else {
            $entries = $this->searchByFiltering($collection, $query);
        }

        if (Collection::whereHandle($collection)->order() === 'date') {
            $format = Config::get('cp.date_format');
            $entries->supplement('date_col_header', function ($entry) use ($format) {
                return $entry->date()->format($format);
            });
        }

        return $entries;
    }

    private function searchCollectionIndex($collection, $query)
    {
        $results = Search::in('collections/'.$collection)->search($query);

        return $this->convertToEntries($results);
    }

    private function searchDefaultIndex($collection, $query)
    {
        $results = Search::get($query);

        return $this->convertToEntries($results)->filter(function ($entry) use ($collection) {
            return $entry->collectionName() === $collection;
        });
    }

    private function convertToEntries($results)
    {
        return collect_entries($results->map(function ($entry) {
            return Entry::find($entry['id']);
        }))->filter();
    }

    private function searchByFiltering($collection, $query)
    {
        return Entry::whereCollection($collection)->filter(function ($entry) use ($query) {
            return Str::contains(strtolower($entry->get('title')), strtolower($query));
        });
    }
}
