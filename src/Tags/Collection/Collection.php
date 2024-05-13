<?php

namespace Statamic\Tags\Collection;

use Statamic\Facades\Entry;
use Statamic\Tags\Concerns;
use Statamic\Tags\Tags;

class Collection extends Tags
{
    use Concerns\OutputsItems;

    protected $defaultAsKey = 'entries';

    /**
     * {{ collection:* }} ... {{ /collection:* }}.
     */
    public function wildcard($method)
    {
        $this->params['from'] = $this->method;

        return $this->index();
    }

    /**
     * {{ collection from="" }} ... {{ /collection }}.
     */
    public function index()
    {
        if (! $this->params->hasAny(['from', 'in', 'folder', 'use', 'collection'])) {
            return $this->context->value('collection');
        }

        $results = $this->entries()->get();

        $results = $this->runHooks('fetched-entries', $results);

        return $this->output($results);
    }

    /**
     * {{ collection:count from="" }} ... {{ /collection:count }}.
     */
    public function count()
    {
        return $this->entries()->count();
    }

    /**
     * {{ collection:next }} ... {{ /collection:next }}.
     */
    public function next()
    {
        $this->params['from'] = $this->currentEntry()->collection()->handle();

        $results = $this->entries()->next($this->currentEntry());

        $this->runHooks('fetched-entries', $results);

        return $this->output($results);
    }

    /**
     * {{ collection:previous }} ... {{ /collection:previous }}.
     */
    public function previous()
    {
        $this->params['from'] = $this->currentEntry()->collection()->handle();

        $results = $this->entries()->previous($this->currentEntry());

        $this->runHooks('fetched-entries', $results);

        return $this->output($results);
    }

    /**
     * {{ collection:older }} ... {{ /collection:older }}.
     */
    public function older()
    {
        $this->params['from'] = $this->currentEntry()->collection()->handle();

        $results = $this->entries()->older($this->currentEntry());

        $results = $this->runHooks('fetched-entries', $results);

        return $this->output($results);
    }

    /**
     * {{ collection:newer }} ... {{ /collection:newer }}.
     */
    public function newer()
    {
        $this->params['from'] = $this->currentEntry()->collection()->handle();

        $results = $this->entries()->newer($this->currentEntry());

        $results = $this->runHooks('fetched-entries', $results);

        return $this->output($results);
    }

    protected function entries()
    {
        return new Entries($this->params);
    }

    protected function currentEntry()
    {
        return Entry::find($this->params->get('current', $this->context->get('id')));
    }
}
