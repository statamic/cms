<?php

namespace Statamic\Tags\Collection;

use Statamic\API\URL;
use Statamic\API\Entry;
use Statamic\Tags\Tags;
use Statamic\Tags\OutputsItems;
use Statamic\Data\Entries\EntryCollection;

class Collection extends Tags
{
    use OutputsItems;

    protected $defaultAsKey = 'entries';

    /**
     * {{ collection:* }} ... {{ /collection:* }}
     */
    public function __call($method, $args)
    {
        $this->parameters['from'] = $this->method;

        return $this->index();
    }

    /**
     * {{ collection from="" }} ... {{ /collection }}
     */
    public function index()
    {
        $entries = $this->entries()->get();

        return $this->output($entries);
    }

    /**
     * {{ collection:count from="" }} ... {{ /collection:count }}
     */
    public function count()
    {
        return $this->entries()->count();
    }

    /**
     * {{ collection:next from="" }} ... {{ /collection:next }}
     */
    public function next()
    {
        $this->parameters['from'] = $this->parameters['from'] ?? $this->currentEntry()->collection()->handle();

        $entries = $this->entries()->next($this->currentEntry());

        return $this->output($entries);
    }

    /**
     * {{ collection:previous from="" }} ... {{ /collection:previous }}
     */
    public function previous()
    {
        $this->parameters['from'] = $this->parameters['from'] ?? $this->currentEntry()->collection()->handle();

        $entries = $this->entries()->previous($this->currentEntry());

        return $this->output($entries);
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
