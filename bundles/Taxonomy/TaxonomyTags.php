<?php

namespace Statamic\Addons\Taxonomy;

use Statamic\API\Helper;
use Statamic\API\Page;
use Statamic\API\Str;
use Statamic\API\Content;
use Statamic\API\Term;
use Statamic\Extend\Tags;
use Statamic\Extend\Management\FilterLoader;
use Statamic\SiteHelpers\Filters as SiteHelperFilters;

class TaxonomyTags extends Tags
{
    /**
     * @var \Statamic\Data\Taxonomies\TermCollection
     */
    protected $terms;

    public function index()
    {
        return $this->listing();
    }

    public function listing()
    {
        $taxonomy = $this->get(['taxonomy', 'is', 'use', 'from', 'folder']);

        return $this->taxonomy($taxonomy);
    }

    public function __call($method, $args)
    {
        $taxonomy = explode(':', $this->tag)[1];

        return $this->taxonomy($taxonomy);
    }

    protected function taxonomy($taxonomy)
    {
        $this->terms = Term::whereTaxonomy($taxonomy);

        // Swap to the appropriate locale. By default it's the site locale.
        $this->terms = $this->terms->localize($this->get('locale', site_locale()));

        $this->filter();

        if ($this->terms->isEmpty()) {
            return $this->parseNoResults();
        }

        $this->terms->supplement('collection', function ($term) {
            return $term->collection();
        });

        $data = $this->terms->toArray();

        if ($as = $this->get('as')) {
            return $this->parse([$as => $data]);
        }

        return $this->parseLoop($data);
    }

    protected function filter()
    {
        if ($this->get('show') !== 'all') {
            $this->filterMinCount();
            $this->filterCollections();
            $this->filterPages();
            $this->filterUnpublished();
            $this->filterFuture();
            $this->filterPast();
            $this->filterSince();
            $this->filterUntil();
            $this->filterConditions();
        }

        $this->sort();

        // Limiting and offsetting should be done after all other filters
        $this->limit();
    }

    private function filterMinCount()
    {
        $min = $this->getInt('min_count', 0);

        if ($min > 0) {
            $this->terms = $this->terms->filter(function($taxonomy) use ($min) {
                return $taxonomy->count() > $min;
            });
        }
    }

    private function filterCollections()
    {
        if (! $collections = $this->get(['collection', 'collections'])) {
            return;
        }

        $collections = Helper::explodeOptions($collections);

        $this->terms = $this->terms->filterContent(function ($content) use ($collections) {
            return $content->filter(function ($item) use ($collections) {
                if ($item instanceof \Statamic\Data\Entries\Entry) {
                    return in_array($item->collectionName(), $collections);
                }
            });
        });
    }

    private function filterPages()
    {
        if (! $pages = $this->get(['page', 'pages'])) {
            return;
        }

        $pages = Helper::explodeOptions($pages);
        $collections = [];

        foreach ($pages as $page) {
            $url = Str::ensureLeft($page, '/');

            if ($content = Page::whereUri($url)) {
                $collections[] = $content->entriesCollection();
            }
        }

        $this->terms = $this->terms->filterContent(function($content) use ($collections) {
            return $content->filter(function($item) use ($collections) {
                return in_array($item->collectionName(), $collections);
            });
        });
    }

    private function filterUnpublished()
    {
        if (! $this->getBool('show_unpublished', false)) {
            $this->terms = $this->terms->filterContent(function($content) {
                return $content->removeUnpublished();
            });
        }
    }

    private function filterFuture()
    {
        if (! $this->getBool('show_future', false)) {
            $this->terms = $this->terms->filterContent(function($content) {
                return $content->removeFuture();
            });
        }
    }

    private function filterPast()
    {
        if (! $this->getBool('show_past', true)) {
            $this->terms = $this->terms->filterContent(function($content) {
                return $content->removePast();
            });
        }
    }

    private function filterSince()
    {
        if ($since = $this->get('since')) {
            $date = array_get($this->context, $since, $since);
            $this->terms = $this->terms->filterContent(function($content) use ($date) {
                return $content->removeBefore($date);
            });
        }
    }

    private function filterUntil()
    {
        if ($until = $this->get('until')) {
            $date = array_get($this->context, $until, $until);
            $this->terms = $this->terms->filterContent(function($content) use ($date) {
                return $content->removeAfter($date);
            });
        }
    }

    private function limit()
    {
        $limit = $this->getInt('limit');
        $limit = ($limit == 0) ? $this->terms->count() : $limit;
        $offset = $this->getInt('offset');

        $this->terms = $this->terms->splice($offset, $limit);
    }

    private function filterConditions()
    {
        if ($filter = $this->get('filter')) {
            // If a "filter" parameter has been specified, we want to use a custom filter class
            // to filter *the taxonomy collection*. If they want to use a custom filter to
            // filter the actual content collection, they can do it from the filter.
            $this->terms = $this->customFilter($filter);
        } else {
            // No filter parameter has been specified, so we should filter the content by condition parameters
            $conditions = array_filter_key($this->parameters, function ($key) {
                return Str::contains($key, ':');
            });

            $this->terms = $this->terms->filterContent(function($content) use ($conditions) {
                return $content->conditions($conditions);
            });
        }
    }

    private function customFilter($filter)
    {
        $class = app(FilterLoader::class)->load($filter, [
            'collection' => $this->terms,
            'context' => $this->context,
            'parameters' => $this->parameters,
        ]);

        if ($class instanceof SiteHelperFilters) {
            $method = Str::studly($filter);
            return $class->$method($this->terms);
        }

        return $class->filter($this->terms);
    }

    private function sort()
    {
        if ($sort = $this->get('sort')) {
            $this->terms = $this->terms->multisort($sort);
        } else {
            // No sort specified? We want to sort by count.
            $this->terms = $this->terms->sortByCount();
        }
    }
}
