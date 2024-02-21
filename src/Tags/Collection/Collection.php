<?php

namespace Statamic\Tags\Collection;

use Statamic\Events\CollectionTagFetchedEntries;
use Statamic\Events\CollectionTagFetchingEntries;
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

        $entries = $this->entries()->get();
        CollectionTagFetchedEntries::dispatch($entries, $this);
        return $this->output(
            $entries
        );
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

        return $this->output(
            $this->entries()->next($this->currentEntry())
        );
    }

    /**
     * {{ collection:previous }} ... {{ /collection:previous }}.
     */
    public function previous()
    {
        $this->params['from'] = $this->currentEntry()->collection()->handle();

        return $this->output(
            $this->entries()->previous($this->currentEntry())
        );
    }

    /**
     * {{ collection:older }} ... {{ /collection:older }}.
     */
    public function older()
    {
        $this->params['from'] = $this->currentEntry()->collection()->handle();

        return $this->output(
            $this->entries()->older($this->currentEntry())
        );
    }

    /**
     * {{ collection:newer }} ... {{ /collection:newer }}.
     */
    public function newer()
    {
        $this->params['from'] = $this->currentEntry()->collection()->handle();

        return $this->output(
            $this->entries()->newer($this->currentEntry())
        );
    }

    protected function entries()
    {
        CollectionTagFetchingEntries::dispatch($this);
        return new Entries($this->params);
    }

    protected function currentEntry()
    {
        return Entry::find($this->params->get('current', $this->context->get('id')));
    }
}
