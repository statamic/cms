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
    public function __call($method, $args)
    {
        $this->parameters['from'] = $this->method;

        return $this->output(
            $this->entries()->get()
        );
    }

    /**
     * {{ collection from="" }} ... {{ /collection }}.
     */
    public function index()
    {
        if (! $this->params->hasAny(['from', 'in', 'folder', 'use', 'collection'])) {
            return $this->context->value('collection');
        }

        return $this->output(
            $this->entries()->get()
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
        $this->parameters['from'] = $this->currentEntry()->collection()->handle();

        return $this->output(
            $this->entries()->next($this->currentEntry())
        );
    }

    /**
     * {{ collection:previous }} ... {{ /collection:previous }}.
     */
    public function previous()
    {
        $this->parameters['from'] = $this->currentEntry()->collection()->handle();

        return $this->output(
            $this->entries()->previous($this->currentEntry())
        );
    }

    /**
     * {{ collection:older }} ... {{ /collection:older }}.
     */
    public function older()
    {
        $this->parameters['from'] = $this->currentEntry()->collection()->handle();

        return $this->output(
            $this->entries()->older($this->currentEntry())
        );
    }

    /**
     * {{ collection:newer }} ... {{ /collection:newer }}.
     */
    public function newer()
    {
        $this->parameters['from'] = $this->currentEntry()->collection()->handle();

        return $this->output(
            $this->entries()->newer($this->currentEntry())
        );
    }

    protected function entries()
    {
        return new Entries($this->parameters);
    }

    protected function currentEntry()
    {
        return Entry::find($this->get('current', $this->context->get('id')));
    }
}
