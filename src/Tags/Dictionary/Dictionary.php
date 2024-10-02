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
        $this->params['handle'] = $tag;

        return $this->index();
    }

    /**
     * {{ dictionary handle="" }} ... {{ /dictionary }}.
     */
    public function index()
    {
        if (! $handle = $this->params->pull('handle')) {
            return [];
        }

        $search = $this->params->pull('search');
        $supplement = $this->params->pull('supplement_data');

        if (! $dictionary = Dictionaries::find($handle, $this->params->all())) {
            throw new DictionaryNotFoundException($handle);
        }

        $options = collect($dictionary->options($search))
            ->map(fn ($label, $value) => new DictionaryItem($supplement ? $dictionary->get($value)->extra() : ['label' => $label, 'value' => $value]))
            ->values();

        $query = (new ItemQueryBuilder)->withItems(new DataCollection($options));

        $this->queryConditions($query);
        $this->queryOrderBys($query);
        $this->queryScopes($query);

        $options = $this->results($query);

        return $this->output($options);
    }
}
