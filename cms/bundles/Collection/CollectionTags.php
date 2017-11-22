<?php

namespace Statamic\Addons\Collection;

use Statamic\API\Collection;
use Statamic\API\Entry;
use Statamic\API\Term;
use Statamic\API\URL;
use Statamic\API\Str;
use Statamic\API\Helper;
use Statamic\API\Request;
use Statamic\Extend\Tags;
use Statamic\Extend\Management\FilterLoader;
use Statamic\Data\Content\ContentCollection;
use Statamic\Presenters\PaginationPresenter;
use Illuminate\Pagination\LengthAwarePaginator;
use Statamic\SiteHelpers\Filters as SiteHelperFilters;

class CollectionTags extends Tags
{
    /**
     * @var \Statamic\Data\Content\ContentCollection
     */
    protected $collection;

    /**
     * @var bool
     */
    private $paginated;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var array
     */
    private $pagination_data;

    /**
     * @var int|null
     */
    private $total_results;

    /**
     * Maps to the {{ collection }} tag.
     *
     * If there's a parameter, we want a listing. Otherwise, just treat it like a variable.
     *
     * @return mixed
     */
    public function index()
    {
        if ($collection = $this->getList(['from', 'folder', 'use'])) {
            return $this->collect($collection);
        }

        $collection = array_get($this->context, 'collection');

        if ($collection instanceof ContentCollection) {
            return $this->collect($collection);
        }

        return $collection;
    }

    /**
     * Maps to `{{ collection:[collection_name] }}`
     *
     * @param string $method  The name of the collection
     * @param array  $args
     * @return string
     */
    public function __call($method, $args)
    {
        return $this->collect(explode(':', $this->tag)[1]);
    }

    /**
     * The brains of the operation
     *
     * @param mixed $collection  Either a collection name string, array of collection names, or a ContentCollection.
     * @return string
     * @throws \Exception
     */
    private function collect($collection)
    {
        $collection = $this->getCollection($collection);

        // Swap to the appropriate locale. By default it's the site locale.
        $this->collection = $collection->localize($this->get('locale', site_locale()));

        // Convert taxonomy fields to actual taxonomy terms.
        // This will allow taxonomy term data to be available in the template without additional tags.
        // If terms are not needed, there's a slight performance benefit in disabling this.
        if ($this->getBool('supplement_taxonomies', true)) {
            $this->collection = $this->collection->supplementTaxonomies();
        }

        $this->filter();

        if ($this->collection->isEmpty()) {
            return $this->parseNoResults();
        }

        return $this->output();
    }

    private function getCollection($collection)
    {
        // If a content collection has been passed in directly, we already have what we need.
        if ($collection instanceof ContentCollection) {
            return $collection;
        }

        // If filtering by taxonomy has been requested, we can simply get the
        // associated content. We don't actually need to "filter" anything.
        if ($taxonomyCollection = $this->getTaxonomyCollection($collection)) {
            return $taxonomyCollection;
        }

        // Otherwise, we'll get all the entries in the specified collection name(s).
        return $this->getEntryCollection($collection);
    }

    private function getEntryCollection($collection)
    {
        $collections = Helper::ensureArray($collection);

        foreach ($collections as $collection) {
            if (! Collection::handleExists($collection)) {
                throw new \Exception("Collection [$collection] doesn't exist.");
            }
        }

        return Entry::whereCollection($collections);
    }

    private function getTaxonomyCollection($collection)
    {
        // If a boolean taxonomy parameter has been provided, retrieve the collection
        // associated with the URI. Otherwise, get it from any taxonomy parameters.
        if ($this->get('taxonomy') === false) {
            return false;
        } elseif ($this->get('taxonomy') === true) {
            $taxonomyCollection = $this->getTaxonomyCollectionFromUri();
        } else {
            $taxonomyCollection = $this->getTaxonomyCollectionFromParams($collection);
        }

        // If neither option returns a collection, then a taxonomy option
        // wasn't selected. We'll just stop right here and move along.
        if (! $taxonomyCollection) {
            return false;
        }

        // Make sure the entries belong to the specified collection(s)
        $collections = Helper::ensureArray($collection);

        return $taxonomyCollection->filter(function ($entry) {
            return $entry instanceof \Statamic\Contracts\Data\Entries\Entry;
        })->filter(function ($entry) use ($collections) {
            return in_array($entry->collectionName(), $collections);
        });
    }

    private function getTaxonomyCollectionFromUri()
    {
        $data = Term::whereSlug(
            array_get($this->context, 'page.default_slug'),
            array_get($this->context, 'page.taxonomy')
        );
        
        return ($data) ? $data->collection() : $data;
    }

    private function getTaxonomyCollectionFromParams($collection)
    {
        // If a taxonomy parameter was provided without any suffix, we will assume that the terms are stored in there.
        if (array_key_exists('taxonomy', $this->parameters)) {
            $terms = $this->parameters['taxonomy'];
            $terms = is_string($terms) ? Helper::explodeOptions($terms) : $terms;
            return $this->getCollectionWhereMultipleTermsExist($collection, $terms);
        }

        // Gather any parameters that match `taxonomy:[handle]`.
        $params = collect($this->parameters)->filterWithKey(function ($value, $key) {
            return Str::startsWith($key, 'taxonomy:');
        });

        // No taxonomy parameters? We're done here.
        if ($params->isEmpty()) {
            return false;
        }

        $collections = $params->map(function ($terms, $param) {
            // The parameter key will denote the taxonomy handle, as well as an optional way of
            // specifying how to treat the parameter values (OR/any or AND/all).
            $parts = explode(':', $param);
            $taxonomy = $parts[1];

            // If any value apart from 'all' or 'and' is specified, 'any' will be used.
            $method = Helper::ensureInArray(array_get($parts, 2), ['any', 'all']);

            // Term slugs may be provided as a string, which may be pipe delimited.
            // They may also reference a field, which means at this point the value may already be an array.
            $terms = (is_string($terms)) ? Helper::explodeOptions($terms) : $terms;
            $terms = array_filter($terms);

            return compact('taxonomy', 'method', 'terms');
        });

        // Remove any parameters with no terms. This may happen if the terms are coming from a
        // variable such an empty/non-existent query parameter, or just a non-existing field.
        $collections = $collections->reject(function ($arr) {
            return empty($arr['terms']);
        });

        // If there are no collections at this point, the parameter values may have all been empty.
        if ($collections->isEmpty()) {
            return $this->getEntryCollection($collection);
        }

        $collections = $collections->map(function ($arr) use ($collection) {
            // Grab the collection using the appropriate method
            $entries = ($arr['method'] === 'any')
                ? $this->getAnyTaxonomyCollection($arr['taxonomy'], $arr['terms'])
                : $this->getAllTaxonomyCollection($arr['taxonomy'], $arr['terms'], $collection);

            // Return the array of entries keyed by the IDs, in preparation for the intersection.
            return $entries->keyBy(function ($entry) {
                return $entry->id();
            })->all();
        });

        // If we're dealing with a single collection, just grab that. Otherwise, we want to get the intersection of
        // all the entries, by their key. Anything returned means that they exist in all of the collections.
        if ($collections->count() === 1) {
            $entries = $collections->first();
        } else {
            $entries = call_user_func_array('array_intersect_key', $collections->values()->all());
        }

        return collect_entries($entries);
    }

    private function getCollectionWhereMultipleTermsExist($collection, $terms)
    {
        return $this
            ->getEntryCollection($collection)
            ->filter(function ($entry) use ($terms) {
                foreach ($terms as $id) {
                    list($taxonomy, $term) = explode('/', $id);
                    $entryTerms = collect(Helper::ensureArray($entry->get($taxonomy, [])));

                    if ($entryTerms->contains($term)) {
                        return true;
                    }
                }
            });
    }

    private function getAllTaxonomyCollection($taxonomy, $slugs, $collection)
    {
        return $this
            ->getEntryCollection($collection)
            ->filter(function ($entry) use ($taxonomy, $slugs) {
                $values = $entry->get($taxonomy, []);
                return $slugs->diff($values)->isEmpty();
            });
    }

    private function getAnyTaxonomyCollection($taxonomy, $slugs)
    {
        $entryCollection = collect_content();

        foreach ($slugs as $slug) {
            $term = Term::whereSlug(Term::normalizeSlug($slug), $taxonomy);
            $collection = ($term) ? $term->collection() : [];
            $entryCollection = $entryCollection->merge($collection);
        }

        return $entryCollection;
    }

    protected function output()
    {
        $as = $this->get('as');

        // Grouping by date requires some pretty different formatting, so
        // we'll want to catch this early on and do something separate.
        if ($this->get('group_by_date')) {
            if ($this->paginated) {
                // todo
                throw new \Exception("
                    Paginating entries grouped by date isn't currently supported.
                    Let us know that you want it.
                ");
            } else {
                return $this->groupByDate();
            }

        } else {
            if ($this->paginated) {
                // Paginated? we need to nest inside a scope key
                $as = $as ?: 'entries';

                $data = [$as => $this->collection->toArray()];

                $data['paginate'] = $this->pagination_data;

                $data = array_merge($data, $this->getCollectionMetaData());

                return $this->parse($data);

            } else {
                // Not paginated, but we can still nest inside a scope key if they have specified to.
                if ($as) {
                    $data = [
                        array_merge(
                            [$as => $this->collection->toArray()],
                            $this->getCollectionMetaData()
                        )
                    ];
                } else {
                    // Add the meta data (total_results, etc) into each iteration.
                    $meta = $this->getCollectionMetaData();
                    $data = collect($this->collection->toArray())->map(function ($item) use ($meta) {
                        return array_merge($item, $meta);
                    })->all();
                }

                return $this->parseLoop($data);
            }
        }
    }

    protected function filter($limit = true)
    {
        $this->filterUnpublished();
        $this->filterPublished();
        $this->filterFuture();
        $this->filterPast();
        $this->filterSince();
        $this->filterUntil();
        $this->filterConditions();

        // Only sort if there's something to sort.
        if (! $this->collection->isEmpty()) {
            $this->sort();
        }

        // Limiting and offsetting should be done after all other filters
        if ($limit) {
            $this->limit();
        }
    }

    private function filterUnpublished()
    {
        if (! $this->getBool('show_unpublished', false)) {
            $this->collection = $this->collection->removeUnpublished();
        }
    }

    private function filterPublished()
    {
        if (! $this->getBool('show_published', true)) {
            $this->collection = $this->collection->removePublished();
        }
    }

    private function filterFuture()
    {
        if (! $this->getBool('show_future', false)) {
            $this->collection = $this->collection->removeFuture();
        }
    }

    private function filterPast()
    {
        if (! $this->getBool('show_past', true)) {
            $this->collection = $this->collection->removePast();
        }
    }

    private function filterSince()
    {
        if ($since = $this->get('since')) {
            $date = array_get($this->context, $since, $since);
            $this->collection = $this->collection->removeBefore($date);
        }
    }

    private function filterUntil()
    {
        if ($until = $this->get('until')) {
            $date = array_get($this->context, $until, $until);
            $this->collection = $this->collection->removeAfter($date);
        }
    }

    private function sort()
    {
        if ($sort = $this->getSortOrder()) {
            $this->collection = $this->collection->multisort($sort)->values();
        }
    }

    /**
     * Get the sort order
     *
     * For the collection tag, it'll sort by the `sort` parameter, or use a
     * sensible default depending on the first item in the collection.
     *
     * Any child classes can (and should) override this method to handle
     * their own sorting strategies.
     */
    protected function getSortOrder()
    {
        // If a sort parameter has been explicitly specified, we're done.
        if ($sort = $this->get('sort')) {
            return $sort;
        }

        // If no sort order has been specified, we'll need to get a sensible default.
        // For date based entries it'll be by date. For number based it'll be by order, etc.
        $type = $this->collection->first()->collection()->order();

        if ($type === 'date') {
            $sort = 'date:desc|title:asc';
        } elseif ($type === 'number') {
            $sort = 'order:asc';
        } else {
            $sort = 'title:asc';
        }

        return $sort;
    }

    /**
     * Get the total number of results in the collection, before pagination.
     *
     * @return int
     */
    protected function getTotalResults()
    {
        return $this->total_results ?: $this->collection->count();
    }

    /**
     * Get any meta data that should be available in templates
     *
     * @return array
     */
    protected function getCollectionMetaData()
    {
        return [
            'total_results' => $this->getTotalResults()
        ];
    }

    protected function limit()
    {
        $limit = $this->getInt('limit');
        $this->limit = ($limit == 0) ? $this->collection->count() : $limit;
        $this->offset = $this->getInt('offset');

        if ($this->getBool('paginate')) {
            $this->paginate();
        } else {
            $this->collection = $this->collection->splice($this->offset, $this->limit);
        }
    }

    private function paginate()
    {
        $this->paginated = true;

        // Keep track of how many items were in the collection before pagination chunks it up.
        $this->total_results = $this->collection->count();

        $page = (int) Request::get('page', 1);

        $this->offset = (($page - 1) * $this->limit) + $this->getInt('offset', 0);

        $items = $this->collection->slice($this->offset, $this->limit);

        $count = $this->collection->count() - $this->getInt('offset', 0);

        $last_page = (int) ceil($count / $this->limit);

        // Fix out of range pagination.
        if ($page > $last_page) {
            // ie. If there are 5 pages of results, and ?page=6 is
            // used, we'll act as though they entered ?page=5.
            $page = $last_page;
        } elseif ($page < 1) {
            // If for some reason the page is less than 1, make it 1.
            $page = 1;
        }

        $paginator = new LengthAwarePaginator($items, $count, $this->limit, $page);

        $paginator->setPath(request()->url());
        $paginator->appends(Request::all());

        $this->pagination_data = [
            'total_items'    => $count,
            'items_per_page' => $this->limit,
            'total_pages'    => $paginator->lastPage(),
            'current_page'   => $paginator->currentPage(),
            'prev_page'      => $paginator->previousPageUrl(),
            'next_page'      => $paginator->nextPageUrl(),
            'auto_links'     => $paginator->render(),
            'links'          => $paginator->render(new PaginationPresenter($paginator))
        ];

        $this->collection = $paginator->getCollection();
    }

    private function filterConditions()
    {
        if ($filter = $this->get('filter')) {
            // If a "filter" parameter has been specified, we want to use a custom filter class.
            $this->collection = $this->customFilter($filter);
        }

        // Filter by condition parameters
        $conditions = array_filter_key($this->parameters, function ($key) {
            return Str::contains($key, ':') && !Str::contains($key, 'taxonomy:');
        });

        if (! empty($conditions)) {
            $this->collection = $this->collection->conditions($conditions);
        }
    }

    private function customFilter($filter)
    {
        $class = app(FilterLoader::class)->load($filter, [
            'collection' => $this->collection,
            'context' => $this->context,
            'parameters' => $this->parameters,
        ]);

        if ($class instanceof SiteHelperFilters) {
            $method = Str::studly($filter);
            return $class->$method($this->collection);
        }

        return $class->filter($this->collection);
    }

    private function groupByDate()
    {
        $data = [];

        if ($param = $this->getList('group_by_date')) {
            $format = $param[0];
            $field = array_get($param, 1, 'date');

            $this->collection = $this->collection->supplement('date_group', function ($entry) use ($format, $field) {
                $date = ($field === 'date')
                        ? $entry->date()
                        : carbon($entry->get($field));

                return $date->format($format);
            });

            $this->collection = $this->collection->groupBy(function($entry) {
                return $entry->getSupplement('date_group');
            });

            $as = $this->get('as', 'entries');

            foreach ($this->collection as $date_group => $entries) {
                $data['date_groups'][] = [
                    'date_group' => $date_group,
                    $as => $entries->toArray()
                ];
            }
        }

        return $data;
    }

    /**
     * Maps to `{{ collection:next }}`
     *
     * @return string
     */
    public function next()
    {
        return $this->sequence('next');
    }

    /**
     * Maps to `{{ collection:previous }}`
     *
     * @return string
     */
    public function previous()
    {
        return $this->sequence('previous');
    }

    /**
     * Gets the next/previous entry sequence.
     * Used by {{ collection:next }} and {{ collection:previous }}
     *
     * @param string $direction  `next` or `previous`
     * @return string
     * @throws \Exception
     */
    protected function sequence($direction)
    {
        if (! $this->collection) {
            $collection = $this->get(['collection', 'in']);

            $this->collection = Entry::whereCollection($collection)->values();
        }

        if ($this->getBool('supplement_taxonomies', true)) {
            $this->collection = $this->collection->supplementTaxonomies();
        }

        $this->filter(false);

        if ($direction === 'previous') {
            $this->collection = $this->collection->reverse();
        }

        if ($this->collection->isEmpty()) {
            return $this->parseNoResults();
        }

        $current = Str::ensureLeft($this->get('current', URL::getCurrent()), '/');
        $current_index = null;

        // Get the index of the 'current' entry
        foreach ($this->collection as $index => $entry) {
            if ($entry->url() === $current) {
                $current_index = $index;
                break;
            }
        }

        // Get the entries from the current one
        $splice = $this->collection->splice($current_index);

        if ($this->getBool('wrap')) {
            // If we're wrapping, put the spliced part at the end.
            $this->collection = $splice->merge($this->collection);
        } else {
            // Otherwise we only want the spliced part.
            $this->collection = $splice;
        }

        // Remove the first item (the current entry) so that we're
        // only left with the rest of the sequence.
        if (! is_null($current_index)) {
            $this->collection->shift();
        }

        // Now we're ready to chop off the excess.
        $this->limit();

        if ($this->collection->isEmpty()) {
            return $this->parseNoResults();
        }

        return $this->output();
    }

    /**
     * Maps to `{{ collection:count }}`
     *
     * @return integer
     */
    public function count()
    {
        $collection = $this->get(['from', 'folder', 'use', 'in', 'collection']);

        $this->collection = Entry::whereCollection($collection);

        $this->filter();

        return $this->collection->count();
    }
}
