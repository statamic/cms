<?php

namespace Statamic\Tags\Dictionary;

use Statamic\Data\DataCollection;
use Statamic\Exceptions\DictionaryNotFoundException;
use Statamic\Facades\Dictionary as Dictionaries;
use Statamic\Query\ItemQueryBuilder;
use Statamic\Tags\Concerns;
use Statamic\Tags\Tags;

class Dictionary extends Tags
{
    use Concerns\GetsQueryResults,
        Concerns\OutputsItems,
        Concerns\QueriesConditions,
        Concerns\QueriesOrderBys,
        Concerns\QueriesScopes;

    protected $defaultAsKey = 'options';

    /**
     * {{ dictionary:* }} ... {{ /dictionary:* }}.
     */
    public function wildcard($tag)
    {
        return $this->loop($tag);
    }

    /**
     * {{ dictionary handle="" }} ... {{ /dictionary }}.
     */
    public function index()
    {
        return $this->loop($this->params->pull('handle'));
    }

    private function loop($handle)
    {
        if (! $handle) {
            return [];
        }

        $search = $this->params->pull('search');

        if (! $dictionary = Dictionaries::find($handle, $this->params->all())) {
            throw new DictionaryNotFoundException($handle);
        }

        $options = (new DataCollection($dictionary->optionItems($search)))
            ->map(fn ($item) => new DictionaryItem($item->toArray()))
            ->values();

        $query = (new ItemQueryBuilder)->withItems($options);

        $this->queryConditions($query);
        $this->queryOrderBys($query);
        $this->queryScopes($query);

        return $this->output($this->results($query));
    }
}
