<?php

namespace Statamic\Search;

use Statamic\Facades\Search;
use Statamic\Tags\OutputsItems;
use Statamic\Tags\Tags as BaseTags;

class Tags extends BaseTags
{
    use OutputsItems;

    protected static $handle = 'search';

    public function results()
    {
        if (! $query = request($this->get('query', 'q'))) {
            return $this->parseNoResults();
        }

        $results = Search::index($this->get('index'))
            ->ensureExists()
            ->search($query)
            ->withData($this->get('supplement_data', true))
            ->limit($this->get('limit'))
            ->offset($this->get('offset'))
            ->get();

        $results = $this->addResultTypes($results);

        return $this->output($results);
    }

    protected function addResultTypes($results)
    {
        return $results->map(function ($result) {
            $type = null;

            if ($result instanceof \Statamic\Contracts\Entries\Entry) {
                $type = 'entry';
            } elseif ($result instanceof \Statamic\Contracts\Taxonomies\Term) {
                $type = 'term';
            } elseif ($result instanceof \Statamic\Contracts\Assets\Asset) {
                $type = 'asset';
            }

            $result->set('result_type', $type);

            return $result;
        });
    }
}
