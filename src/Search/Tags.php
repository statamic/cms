<?php

namespace Statamic\Search;

use Statamic\Facades\Search;
use Statamic\Facades\Site;
use Statamic\Tags\Concerns;
use Statamic\Tags\Tags as BaseTags;

class Tags extends BaseTags
{
    use Concerns\GetsQueryResults {
        results as getQueryResults;
    }
    use Concerns\OutputsItems,
        Concerns\QueriesConditions,
        Concerns\QueriesOrderBys,
        Concerns\QueriesScopes;

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
