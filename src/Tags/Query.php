<?php

namespace Statamic\Tags;

use Statamic\Tags\Concerns;
use Statamic\Tags\Tags;

class Query extends Tags
{
    use Concerns\QueriesConditions,
        Concerns\QueriesScopes,
        Concerns\QueriesOrderBys,
        Concerns\GetsQueryResults,
        Concerns\OutputsItems;

    /**
     * {{ query builder="" }} ... {{ /query }}
     */
    public function index()
    {
        return $this->evaluate($this->parameters->get('builder'));
    }

    /**
     * {{ query:* }} ... {{ /query:* }}
     */
    public function wildcard($tag)
    {
        return $this->evaluate($this->context->get($tag));
    }

    protected function evaluate($query)
    {
        $this->queryConditions($query);
        $this->queryScopes($query);
        $this->queryOrderBys($query);

        return $this->output($this->results($query));
    }
}
