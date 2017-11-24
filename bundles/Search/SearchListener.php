<?php

namespace Statamic\Addons\Search;

use Statamic\API\Config;
use Statamic\API\Search;
use Statamic\Stache\Stache;
use Statamic\Extend\Listener;
use Statamic\Events\Stache\RepositoryItemRemoved;
use Statamic\Events\Stache\RepositoryItemInserted;

class SearchListener extends Listener
{
    public $events = [
        RepositoryItemInserted::class => 'insert',
        RepositoryItemRemoved::class => 'remove',
    ];

    /**
     * @var Stache
     */
    private $stache;

    /**
     * @param Stache $stache
     */
    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
    }

    /**
     * Insert content into the search index
     *
     * @param RepositoryItemInserted $event
     */
    public function insert(RepositoryItemInserted $event)
    {
        if (! Config::get('search.auto_index')) {
            return;
        }

        // If the cache was cleared, all the content will be re-added, triggering this event for each item.
        // Typically, these will be considered 'false positives' since the search index probably has the
        // same content. If using an API driver like Algolia, there would be an API request for each
        // item, which would be nuts. So, while the Stache is warming up, we'll disable indexing.
        if ($this->stache->isCold()) {
            return;
        }

        $content = $event->item;

        if (! is_object($content) || ! method_exists($content, 'id')) {
            return;
        }

        Search::insert($event->id, $content->toArray());
    }

    /**
     * Delete an item from the search index
     *
     * @param RepositoryItemRemoved $event
     */
    public function remove(RepositoryItemRemoved $event)
    {
        if (! Config::get('search.auto_index')) {
            return;
        }

        $content = $event->item;

        if (! is_object($content) || ! method_exists($content, 'id')) {
            return;
        }

        Search::delete($event->id);
    }
}
