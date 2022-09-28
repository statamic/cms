<?php

namespace Statamic\Search;

use Statamic\Facades\Search;
use Statamic\Facades\Site;
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
            ->withData($supplementData)
            ->limit($this->params->get('limit'))
            ->offset($this->params->get('offset'));

        $this->querySite($builder);
        $this->queryStatus($builder);
        $this->queryConditions($builder);
        $this->queryScopes($builder);
        $this->queryOrderBys($builder);

        $results = $this->getQueryResults($builder);

        // Backwards compatibility. This can be removed in 3.2.
        if (! $this->params->get('as')) {
            return $this->output($this->addResultTypes($results));
        }

        $results = $this->output($results);

        return $this->addResultTypesToOutput($results);
    }

    protected function addResultTypesToOutput($output)
    {
        if (! $this->params->get('paginate') && ! $this->params->get('as')) {
            return $this->addResultTypes($output);
        }

        $as = $this->getPaginationResultsKey();

        $output[$as] = $this->addResultTypes($output[$as]);

        return $output;
    }

    protected function addResultTypes($results)
    {
        return $results->map(function ($result) {
            $reference = is_array($result) ? $result['reference'] : $result->reference();

            [$type, $id] = explode('::', $reference, 2);

            if (is_array($result)) {
                $result['result_type'] = $type;
            } else {
                $result->setSupplement('result_type', $type);
            }

            return $result;
        });
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
