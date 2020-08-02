<?php

namespace Statamic\Tags;

use Statamic\Entries\EntryCollection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Tags\Collection\Collection;

class GetContent extends Collection
{
    /**
     * {{ get_content:* }} ... {{ /get_content:* }}.
     */
    public function __call($method, $args)
    {
        $from = Arr::get($this->context, $method)->raw();

        if (is_array($from)) {
            $from = implode('|', $from);
        }

        $this->parameters['from'] = $from;

        return $this->index();
    }

    /**
     * {{ get_content from="" }} ... {{ /get_content }}.
     */
    public function index()
    {
        $from = $this->getList(['from', 'id']);

        if (Str::startsWith($from[0], '/')) {
            $site = $this->params->get(['site', 'locale'], Site::current()->handle());

            $entries = EntryCollection::make($from)->map(function ($item) use ($site) {
                return Entry::findByUri($item, $site);
            });

            return $this->output($entries);
        }

        // TODO: Support multiple IDs.
        if (count($from) > 1) {
            throw new \Exception('The get_content tag currently only supports getting a single item by ID.');
        }

        $this->parameters['id:matches'] = $from[0];
        $this->parameters['from'] = '*';

        return parent::index();
    }
}
