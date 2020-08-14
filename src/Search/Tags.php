<?php

namespace Statamic\Search;

use Statamic\Facades\Search;
use Statamic\Tags\Concerns;
use Statamic\Tags\Tags as BaseTags;

class Tags extends BaseTags
{
    use Concerns\OutputsItems,
        Concerns\QueriesConditions;

    protected static $handle = 'search';

    public function results()
    {
        if (! $query = request($this->params->get('query', 'q'))) {
            return $this->parseNoResults();
        }

        $builder = Search::index($this->params->get('index'))
            ->ensureExists()
            ->search($query)
            ->withData($this->params->get('supplement_data', true))
            ->limit($this->params->get('limit'))
            ->offset($this->params->get('offset'));

        $this->queryConditions($builder);

        $results = $this->addResultTypes($builder->get());

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
