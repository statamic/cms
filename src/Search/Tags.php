<?php

namespace Statamic\Search;

use Statamic\Facades\Search;
use Statamic\Facades\Site;
use Statamic\Search\PlainResult;
use Statamic\Search\Result;
use Statamic\Tags\Concerns;
use Statamic\Tags\Tags as BaseTags;

class Tags extends BaseTags
{
    use Concerns\OutputsItems,
        Concerns\QueriesConditions,
        Concerns\QueriesScopes,
        Concerns\QueriesOrderBys;
    use Concerns\GetsQueryResults {
        results as getQueryResults;
    }

    protected static $handle = 'search';

    public function results()
    {
        if (! $query = $this->params->get('for') ?? request($this->params->get('query', 'q'))) {
            return $this->parseNoResults();
        }

        $supplementData = $this->params->get('supplement_data', true);

        $builder = Search::index($this->params->get('index'))
            ->ensureExists()
            ->search($query)
            ->withData($supplementData);

        $this->querySite($builder);
        $this->queryStatus($builder);
        $this->queryConditions($builder);
        $this->queryScopes($builder);
        $this->queryOrderBys($builder);

        $results = $this->getQueryResults($builder);

        // PlainResult inherits from Result, but doesnt provide getSearchable...
        if (method_exists($results, 'getCollection')) {
            if ($results->getCollection()->first() instanceof Result && !$results->getCollection()->first() instanceof PlainResult) {
                $results->setCollection($results->getCollection()->map->getSearchable());
            }
        } else if ($results->first() instanceof Result && !$results->first() instanceof PlainResult) {
            $results->transform->getSearchable();
        }

        return $this->output($results);
    }

    protected function queryStatus($query)
    {
        if ($this->isQueryingCondition('status') || $this->isQueryingCondition('published')) {
            return;
        }

        return $query->where('status', 'published');
    }

    protected function querySite($query)
    {
        $site = $this->params->get(['site', 'locale'], Site::current()->handle());

        if ($site === '*' || ! Site::hasMultiple()) {
            return;
        }

        return $query->where('site', $site);
    }
}
