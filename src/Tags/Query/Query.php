<?php

namespace Statamic\Tags\Query;

use Statamic\Tags\Tags;
use Statamic\Tags\OutputsItems;

class Query extends Tags
{
    use GetsResults,
        OutputsItems,
        HasConditions,
        HasOrderBys,
        HasScopes;

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
