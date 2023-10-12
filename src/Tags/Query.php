<?php

namespace Statamic\Tags;

class Query extends Tags
{
    use Concerns\GetsQueryResults,
        Concerns\OutputsItems,
        Concerns\QueriesConditions,
        Concerns\QueriesOrderBys,
        Concerns\QueriesScopes;

    /**
     * {{ query builder="" }} ... {{ /query }}.
     */
    public function index()
    {
        return $this->evaluate($this->params->get('builder'));
    }

    /**
     * {{ query:* }} ... {{ /query:* }}.
     */
    public function wildcard($tag)
    {
        return $this->evaluate($this->context->value($tag));
    }

    protected function evaluate($query)
    {
        $this->queryConditions($query);
        $this->queryScopes($query);
        $this->queryOrderBys($query);

        return $this->output($this->results($query));
    }
}
