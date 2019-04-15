<?php

namespace Statamic\Tags\Collection;

use Statamic\API;

class Entries
{
    protected $collection;
    protected $parameters;
    protected $paginate;
    protected $perPage;
    protected $showPublished = true;
    protected $showUnpublished = false;
    protected $showPast = true;
    protected $showFuture = false;
    protected $ignoredParams = ['from', 'as'];

    public function __construct($collection, $parameters)
    {
        $this->collection = API\Collection::whereHandle($collection);
        $this->parameters = $this->parseParameters($parameters);
    }

    public function get()
    {
        $query = $this->collection->queryEntries();

        // If neither published nor unpublished entries should be shown, we'll just have nothing to show.
        if (! $this->showPublished && ! $this->showUnpublished) {
            return collect_entries();
        }

        $this->queryPublished($query);
        $this->queryPastFuture($query);

        foreach ($this->parameters as $key => $value) {
            // TODO: any tag parameters that don't translate 1:1 with query methods
            // should be accounted for right here.

            $query->$key($value);
        }

        return $this->paginate ? $query->paginate($this->perPage) : $query->get();
    }

    protected function parseParameters($params)
    {
        $params = array_except($params, $this->ignoredParams);

        if ($this->paginate = array_pull($params, 'paginate', false)) {
            $this->perPage = array_pull($params, 'limit');
        }

        $this->showPublished = array_pull($params, 'show_published', true);
        $this->showUnpublished = array_pull($params, 'show_unpublished', false);

        $this->showPast = array_pull($params, 'show_past', true);
        $this->showFuture = array_pull($params, 'show_future', false);

        return $params;
    }

    protected function queryPublished($query)
    {
        if ($this->showPublished && $this->showUnpublished) {
            return;
        } elseif ($this->showPublished && ! $this->showUnpublished) {
            $query->where('published', true);
        } elseif (! $this->showPublished && $this->showUnpublished) {
            $query->where('published', false);
        }
    }

    protected function queryPastFuture($query)
    {

    }
}
