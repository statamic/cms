<?php

namespace Statamic\Tags\Collection;

use Illuminate\Support\Carbon;
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
        $this->params['from'] = $this->method;

        return $this->outputIndex();
    }

    /**
     * {{ collection from="" }} ... {{ /collection }}.
     */
    public function index()
    {
        if (! $this->params->hasAny(['from', 'in', 'folder', 'use', 'collection'])) {
            return $this->context->value('collection');
        }

        return $this->outputIndex();
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
        return new Entries($this->params);
    }

    protected function currentEntry()
    {
        return Entry::find($this->params->get('current', $this->context->get('id')));
    }

    protected function outputIndex()
    {
        $entries = $this->entries()->get();

        if (! $this->params->get('group_by_date')) {
            return $this->output($entries);
        }

        if ($this->params->get('paginate')) {
            throw new \Exception("Paginating entries grouped by date isn't currently supported.");
        }

        return $this->output($this->groupByDate($entries));
    }

    protected function groupByDate($entries)
    {
        [$format, $field] = array_replace([null, null], explode('|', $this->params['group_by_date']));

        return collect($entries)->groupBy(function ($entry) use ($format, $field) {
            return ($field
                ? Carbon::parse($entry->get($field))
                : $entry->date()
            )->format($format);
        })->map(function ($entries, $date) {
            return ['date' => $date, 'entries' => $entries];
        })->values();
    }
}
