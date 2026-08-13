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
        $this->queryTaxonomyDescendants($query);
        $this->queryConditions($query);
        $this->queryScopes($query);
        $this->queryOrderBys($query);

        return $this->output($this->results($query));
    }

    /**
     * Allows `{{ entries with_descendants="true" }}` on a hierarchical taxonomy
     * term page to include entries tagged with any descendant term.
     */
    private function queryTaxonomyDescendants($query)
    {
        if (! $this->params->bool('with_descendants')) {
            return;
        }

        try {
            $query->withTaxonomyDescendants();
        } catch (\BadMethodCallException $e) {
            // The builder doesn't query taxonomized entries. The param is a no-op.
        }
    }
}
