<?php

namespace Statamic\StaticCaching;

use Statamic\API\Config;
use Statamic\Contracts\Data\Pages\Page;
use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Contracts\Data\Content\Content;
use Statamic\Contracts\Data\Taxonomies\Term;
use Statamic\Events\Stache\RepositoryItemRemoved;
use Statamic\Events\Stache\RepositoryItemInserted;

class Invalidator
{
    /**
     * @var array
     */
    protected $rules;

    /**
     * @var \Statamic\StaticCaching\Cacher
     */
    private $cacher;

    /**
     * @param \Statamic\StaticCaching\Cacher $cacher
     */
    public function __construct(Cacher $cacher)
    {
        $this->cacher = $cacher;
    }

    /**
     * Register the listeners for the subscriber
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(RepositoryItemInserted::class, self::class.'@handle');
        $events->listen(RepositoryItemRemoved::class, self::class.'@handle');
    }

    /**
     * Handle the event and invalidate the appropriate urls
     *
     * @param RepositoryItemInserted|RepositoryItemRemoved $event
     * @return void
     */
    public function handle($event)
    {
        $content = $event->item;

        if (! $content instanceof Content) {
            return;
        }

        // Get the invalidation rule scheme
        $this->rules = $this->cacher->config('invalidation');

        // If we've opted to clear all items, we'll just flush it all and call it a day.
        if ($this->rules === 'all') {
            $this->cacher->flush();
            return;
        }

        // Invalidate the content's own URL.
        $this->invalidateUrl($content->url());

        // Call the specialized method based on the content type, eg. invalidateEntryUrls
        $method = 'invalidate'.ucfirst($content->contentType()).'Urls';
        if (method_exists($this, $method)) {
            $this->$method($content);
        }
    }

    /**
     * Invalidate a specific URL
     *
     * @param string $url
     */
    protected function invalidateUrl($url)
    {
        $this->cacher->invalidateUrl($url);
    }

    /**
     * Invalidate URLs for an entry
     *
     * @param Entry $entry
     */
    protected function invalidateEntryUrls(Entry $entry)
    {
        $collection = $entry->collectionName();

        $urls = array_get($this->rules, "collections.$collection.urls", []);

        $this->cacher->invalidateUrls($urls);
    }

    /**
     * Invalidate URLs for a taxonomy term
     *
     * @param Term $term
     */
    protected function invalidateTermUrls(Term $term)
    {
        $taxonomy = $term->taxonomyName();

        $urls = array_get($this->rules, "taxonomies.$taxonomy.urls", []);

        $this->cacher->invalidateUrls($urls);
    }

    protected function invalidatePageUrls(Page $page)
    {
        $url = $page->url();

        $urls = array_get($this->rules, "pages.$url.urls", []);

        $this->cacher->invalidateUrls($urls);
    }
}
