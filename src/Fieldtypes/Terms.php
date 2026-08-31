<?php

namespace Statamic\Fieldtypes;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Data\Localization;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\CP\Column;
use Statamic\Exceptions\TaxonomyNotFoundException;
use Statamic\Exceptions\TermsFieldtypeBothOptionsUsedException;
use Statamic\Exceptions\TermsFieldtypeTaxonomyOptionUsed;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Scope;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\GraphQL\Types\TermInterface;
use Statamic\Http\Resources\CP\Taxonomies\TermsFieldtypeTerms as TermsResource;
use Statamic\Query\OrderBy;
use Statamic\Query\OrderedQueryBuilder;
use Statamic\Query\Scopes\Filter;
use Statamic\Query\Scopes\Filters\Fields\Terms as TermsFilter;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Taxonomies\EnsuresTermPaths;

use function Statamic\trans as __;

class Terms extends Relationship
{
    use UpdatesReferences;
    protected $canEdit = true;
    protected $canCreate = true;
    protected $canSearch = true;
    protected $statusIcons = false;
    protected $taggable = true;
    protected $icon = 'fieldtype-taxonomy';
    protected $formComponent = 'term-publish-form';

    protected $formComponentProps = [
        'initialActions' => 'actions',
        'initialTitle' => 'title',
        'initialReference' => 'reference',
        'initialFieldset' => 'blueprint',
        'initialValues' => 'values',
        'initialLocalizedFields' => 'localizedFields',
        'initialMeta' => 'meta',
        'initialPermalink' => 'permalink',
        'initialLocalizations' => 'localizations',
        'initialHasOrigin' => 'hasOrigin',
        'initialOriginValues' => 'originValues',
        'initialOriginMeta' => 'originMeta',
        'initialSite' => 'locale',
        'initialIsWorkingCopy' => 'hasWorkingCopy',
        'initialReadOnly' => 'readOnly',
        'revisionsEnabled' => 'revisionsEnabled',
        'taxonomyHandle' => 'taxonomy',
    ];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Input Behavior'),
                'fields' => [
                    'taxonomies' => [
                        'display' => __('Taxonomies'),
                        'instructions' => __('statamic::fieldtypes.terms.config.taxonomies'),
                        'type' => 'taxonomies',
                        'mode' => 'select',
                        'width' => '50',
                    ],
                    'create' => [
                        'display' => __('Allow Creating New Terms'),
                        'instructions' => __('statamic::fieldtypes.terms.config.create'),
                        'type' => 'toggle',
                        'default' => true,
                        'width' => '50',
                    ],
                ],
            ],
            [
                'display' => __('Appearance'),
                'fields' => [
                    'mode' => [
                        'display' => __('UI Mode'),
                        'instructions' => __('statamic::fieldtypes.relationship.config.mode'),
                        'type' => 'radio',
                        'default' => 'default',
                        'options' => [
                            'default' => __('Stack Selector'),
                            'select' => __('Select Dropdown'),
                            'typeahead' => __('Typeahead Field'),
                        ],
                    ],
                ],
            ],
            [
                'display' => __('Boundaries & Limits'),
                'fields' => [
                    'max_items' => [
                        'display' => __('Max Items'),
                        'instructions' => __('statamic::messages.max_items_instructions'),
                        'min' => 1,
                        'type' => 'integer',
                    ],
                ],
            ],
            [
                'display' => __('Advanced'),
                'fields' => [
                    'show_query_scopes' => [
                        'display' => __('Query Scopes'),
                        'instructions' => __('statamic::fieldtypes.terms.config.query_scopes'),
                        'type' => 'revealer',
                        'input_label' => __('Apply Query Scopes'),
                        'default' => false,
                        'width' => '50',
                    ],
                    'query_scopes' => [
                        'display' => __('Query Scopes'),
                        'instructions' => __('statamic::fieldtypes.terms.config.query_scopes'),
                        'type' => 'taggable',
                        'options' => Scope::all()
                            ->reject(fn ($scope) => $scope instanceof Filter)
                            ->map->handle()
                            ->values()
                            ->all(),
                        'width' => '50',
                        'if' => [
                            'show_query_scopes' => 'true',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function filter()
    {
        return new TermsFilter($this);
    }

    public function preload()
    {
        $taxonomy = $this->usingSingleTaxonomy()
            ? Taxonomy::findByHandle($this->taxonomies()[0])
            : null;

        $preload = parent::preload();

        if ($this->hasHierarchicalTaxonomy() && ($this->field->get('mode') ?? 'default') === 'default') {
            $preload['mode'] = 'select';
        }

        if (! $taxonomy || ! $taxonomy->hasStructure()) {
            return $preload;
        }

        $blueprints = $taxonomy
            ->termBlueprints()
            ->reject->hidden()
            ->map(function ($blueprint) {
                return [
                    'handle' => $blueprint->handle(),
                    'title' => $blueprint->title(),
                ];
            })->values();

        return array_merge($preload, ['tree' => [
            'title' => $taxonomy->title(),
            'url' => cp_route('taxonomies.tree.index', $taxonomy->handle()),
            'showSlugs' => false,
            'expectsRoot' => false,
            'blueprints' => $blueprints,
        ]]);
    }

    /**
     * The single configured taxonomy, if it's hierarchical.
     */
    public function hierarchicalTaxonomy()
    {
        if (! $this->usingSingleTaxonomy()) {
            return null;
        }

        $taxonomy = Taxonomy::findByHandle($this->taxonomies()[0]);

        return $taxonomy && $taxonomy->hierarchical() ? $taxonomy : null;
    }

    /**
     * Hierarchical taxonomies available to this field, including when several are configured.
     */
    public function hierarchicalTaxonomies(): Collection
    {
        $handles = ! empty($this->taxonomies())
            ? $this->taxonomies()
            : $this->getConfiguredTaxonomies();

        return collect($handles)
            ->map(fn ($handle) => Taxonomy::findByHandle($handle))
            ->filter()
            ->filter->hierarchical()
            ->values();
    }

    public function hasHierarchicalTaxonomy(): bool
    {
        return $this->hierarchicalTaxonomies()->isNotEmpty();
    }

    public function augment($values)
    {
        $single = $this->config('max_items') === 1;

        // The parent is the item this terms fieldtype exists on. Most commonly an
        // entry, but could also be something else, like another taxonomy term.
        $parent = $this->field->parent();

        $site = $parent && $parent instanceof Localization
            ? $parent->locale()
            : Site::current()->handle(); // Use the "current" site so this will get localized appropriately on the front-end.

        if ($single && Blink::has($key = 'terms-augment-'.$site.'-'.json_encode($values))) {
            return Blink::get($key);
        }

        $query = $this->queryBuilder($values);

        return $single && ! config('statamic.system.always_augment_to_query', false)
            ? Blink::once($key, fn () => $query->first())
            : $query;
    }

    private function queryBuilder($values)
    {
        // The parent is the item this terms fieldtype exists on. Most commonly an
        // entry, but could also be something else, like another taxonomy term.
        $parent = $this->field->parent();

        $site = $parent && $parent instanceof Localization
            ? $parent->locale()
            : Site::current()->handle(); // Use the "current" site so this will get localized appropriately on the front-end.

        $ids = $this->convertAugmentationValuesToIds($values);

        $query = (new OrderedQueryBuilder(Term::query(), $ids))
            ->whereIn('id', $ids)
            ->where('site', $site);

        $shouldQueryCollection = $this->usingSingleTaxonomy()
            && ! $this->field->parentField()
            && $parent
            && $parent instanceof Entry
            && $this->field->handle() === $this->taxonomies()[0]
            && $parent->collection() !== null
            && $parent->collection()->taxonomies()->map->handle()->contains($this->field->handle());

        if ($shouldQueryCollection) {
            $query->where('collection', $parent->collectionHandle());
        }

        return $query;
    }

    private function convertAugmentationValuesToIds($values)
    {
        $taxonomy = $this->usingSingleTaxonomy()
            ? $this->taxonomies()[0]
            : null;

        return collect(Arr::wrap($values))->map(function ($value) use ($taxonomy) {
            if ($taxonomy) {
                if (is_string($value) && str_contains($value, EnsuresTermPaths::DELIMITER) && $this->hierarchicalTaxonomy()) {
                    $value = (new EnsuresTermPaths)->slugFromValue($value, hierarchical: true);
                }

                return "{$taxonomy}::{$value}";
            } else {
                if (! Str::contains($value, '::')) {
                    throw new \Exception("Ambigious taxonomy term value [$value]. Field [{$this->field->handle()}] is configured with multiple taxonomies.");
                }

                return $value;
            }
        })->all();
    }

    public function shallowAugment($values)
    {
        $items = $this->augment($values);

        if ($this->config('max_items') === 1) {
            $items = collect([$items]);
        } else {
            $items = $items->get();
        }

        $items = $items->filter()->map(function ($item) {
            return $item->toShallowAugmentedCollection();
        })->collect();

        return $this->config('max_items') === 1 ? $items->first() : $items;
    }

    public function process($data)
    {
        $data = parent::process($data);

        if ($this->usingSingleTaxonomy()) {
            $taxonomy = $this->taxonomies()[0];
            $data = collect($data)->map(function ($id) use ($taxonomy) {
                if (! Str::contains($id, '::')) {
                    $id = $this->createTermFromString($id, $taxonomy);
                }

                if (! $id) {
                    return null;
                }

                return explode('::', $id, 2)[1];
            })
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($this->field->get('max_items') === 1) {
                return $data[0] ?? null;
            }
        }

        return $data;
    }

    public function preProcess($data)
    {
        $data = parent::preProcess($data);

        if ($this->usingSingleTaxonomy()) {
            $taxonomy = $this->taxonomies()[0];
            $data = collect($data)->map(function ($id) use ($taxonomy) {
                if (! Str::contains($id, '::')) {
                    $id = "{$taxonomy}::{$id}";
                }

                return $id;
            })->all();
        }

        return $data;
    }

    public function getIndexItems($request)
    {
        if ($this->config('mode') == 'typeahead' && ! $request->search) {
            return collect();
        }

        // When the user can't view any of the configured taxonomies, return an empty result
        // set instead of throwing. The picker treats this like the filter-to-viewable case.
        if ($this->getViewableTaxonomies($this->getConfiguredTaxonomies())->isEmpty()) {
            return collect();
        }

        $query = $this->getIndexQuery($request);

        if ($this->shouldOrderByHierarchy($request)) {
            return $this->orderItemsByHierarchy($query->get());
        }

        if ($sort = $this->getSortColumn($request)) {
            $query->orderBy($sort, $this->getSortDirection($request));
        }

        return $request->boolean('paginate', true) ? $query->paginate($request->filled('perPage') ? Statamic::cpPerPage($request->integer('perPage')) : 15) : $query->get();
    }

    /**
     * Select/typeahead dropdowns for a hierarchical taxonomy should list options
     * in tree order (so they can be indented), unless the user is searching
     * or explicitly sorting. Paginated (stack selector) requests keep
     * regular ordering since tree order is meaningless across pages.
     */
    private function shouldOrderByHierarchy($request): bool
    {
        return ! $request->sort
            && ! $request->search
            && ! $request->boolean('paginate', true)
            && $this->hasHierarchicalTaxonomy();
    }

    private function orderItemsByHierarchy(Collection $items): Collection
    {
        $orders = $this->hierarchicalTaxonomies()->mapWithKeys(function ($taxonomy) {
            return [$taxonomy->handle() => $taxonomy->structure()->tree()->flattenedPages()->map->id()->flip()];
        });

        return $items
            ->sortBy(function ($term) use ($orders) {
                $handle = $term->taxonomyHandle();
                $order = $orders->get($handle);

                if (! $order) {
                    return [$handle, PHP_INT_MAX, $term->slug()];
                }

                return [$handle, $order->get($term->inDefaultLocale()->slug(), PHP_INT_MAX)];
            })
            ->values();
    }

    private function getViewableTaxonomies(array $taxonomies): Collection
    {
        $user = User::current();

        return collect($taxonomies)
            ->map(fn (string $taxonomyHandle) => Taxonomy::findByHandle($taxonomyHandle))
            ->filter()
            ->filter(fn ($taxonomy) => $user->can('view', $taxonomy));
    }

    public function getResourceCollection($request, $items)
    {
        // Derive columns only from a taxonomy the user can view. With none viewable, return
        // empty data and no columns rather than leaking the structure of an unviewable blueprint.
        if (! $taxonomy = $this->getColumnTaxonomy($request)) {
            return JsonResource::collection($items)->additional(['meta' => ['columns' => []]]);
        }

        return (new TermsResource($items, $this))
            ->blueprint($taxonomy->termBlueprint())
            ->columnPreferenceKey("taxonomies.{$taxonomy->handle()}.columns");
    }

    protected function getBlueprint($request = null)
    {
        return $this->getColumnTaxonomy($request)?->termBlueprint();
    }

    protected function getColumnTaxonomy($request = null)
    {
        $taxonomy = $this->getFirstTaxonomyFromRequest($request);

        // Only derive columns from a taxonomy the user can view. If the first configured
        // taxonomy isn't viewable, fall back to the first viewable configured taxonomy,
        // or none at all when the user can view none of them.
        return User::current()->can('view', $taxonomy)
            ? $taxonomy
            : $this->getViewableTaxonomies($this->getConfiguredTaxonomies())->first();
    }

    protected function getFirstTaxonomyFromRequest($request)
    {
        $taxonomies = $this->getConfiguredTaxonomies();

        $taxonomy = Taxonomy::findByHandle($taxonomyHandle = Arr::first($taxonomies));

        throw_if(! $taxonomy, new TaxonomyNotFoundException($taxonomyHandle));

        return $taxonomy;
    }

    public function getSortColumn($request)
    {
        $column = OrderBy::column($request->get('sort'));

        if (! $column && ! $request->search) {
            $column = 'title'; // todo: get from taxonomy or config
        }

        return $column;
    }

    public function getSortDirection($request)
    {
        $order = $request->get('order', 'asc');

        if (! $request->sort && ! $request->search) {
            // $order = 'asc'; // todo: get from taxonomy or config
        }

        return $order;
    }

    protected function getBaseSelectionsUrlParameters()
    {
        return [
            'taxonomies' => $this->taxonomies(),
        ];
    }

    protected function getCreatables()
    {
        if ($url = $this->getCreateItemUrl()) {
            return [['url' => $url]];
        }

        $taxonomies = $this->getConfiguredTaxonomies();

        $user = User::current();

        return collect($taxonomies)->flatMap(function ($taxonomyHandle) use ($user) {
            $taxonomy = Taxonomy::findByHandle($taxonomyHandle);

            throw_if(! $taxonomy, new TaxonomyNotFoundException($taxonomyHandle));

            if (! $user->can('create', [TermContract::class, $taxonomy])) {
                return null;
            }

            $blueprints = $taxonomy->termBlueprints();

            return $blueprints->map(function ($blueprint) use ($taxonomy) {
                return [
                    'parent_title' => $taxonomy->title(),
                    'blueprint' => $blueprint->title(),
                    'url' => cp_route('taxonomies.terms.create', [$taxonomy->handle(), Site::selected()->handle()]).'?blueprint='.$blueprint->handle(),
                ];
            });
        })->all();
    }

    protected function authorizeItemData($id): bool
    {
        return $this->authorizeViewable($this->findTerm($id));
    }

    protected function toItemArray($id)
    {
        $id = $this->normalizeTermId($id);

        if (! $term = $this->findTerm($id)) {
            return $this->invalidItemArray($id);
        }

        // The parent is the item this terms fieldtype exists on. Most commonly an
        // entry, but could also be something else, like another taxonomy term.
        $parent = $this->field->parent();

        $locale = $parent && $parent instanceof Localization
            ? $parent->locale()
            : Site::default()->handle();

        $term = $term->in($locale);

        return [
            'id' => $id,
            'reference' => $term->reference(),
            'title' => $term->value('title'),
            'published' => $term->published(),
            'private' => $term->private(),
            'edit_url' => $term->editUrl(),
            'editable' => User::current()->can('edit', $term),
            'hint' => $this->getItemHint($term),
            ...$this->itemHierarchyMeta($term),
        ];
    }

    /**
     * Depth, slug path, and structured ancestor titles for the relationship UI.
     */
    public function itemHierarchyMeta($term): array
    {
        $meta = [];

        if (count($this->getConfiguredTaxonomies()) > 1) {
            $meta['taxonomy_title'] = __($term->taxonomy()->title());
        }

        if (! $term->taxonomy()?->hierarchical()) {
            return $meta;
        }

        $ancestors = $term->ancestors();

        return [
            ...$meta,
            'depth' => $term->depth() ?? 1,
            'path' => $ancestors->map->slug()->push($term->slug())->implode(EnsuresTermPaths::DELIMITER),
            'ancestors' => $ancestors->map->title()->values()->all(),
        ];
    }

    protected function normalizeTermId($id): string
    {
        if ($this->usingSingleTaxonomy() && ! Str::contains($id, '::')) {
            return "{$this->taxonomies()[0]}::{$id}";
        }

        return $id;
    }

    protected function findTerm($id)
    {
        $id = $this->normalizeTermId($id);

        return $this->itemCache[$id] ??= Term::find($id);
    }

    protected function getColumns()
    {
        $columns = [Column::make('title')];

        if (! $this->usingSingleTaxonomy()) {
            $columns[] = Column::make('taxonomy');
        }

        return $columns;
    }

    protected function getIndexQuery($request)
    {
        $query = Term::query();

        $taxonomies = $this->getViewableTaxonomies($this->getConfiguredTaxonomies())
            ->map->handle()
            ->all();

        $query->whereIn('taxonomy', $taxonomies);

        if ($search = $request->search) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        if ($site = $request->site) {
            $query->where('site', $site);
        }

        $exclusions = collect($request->exclusions ?? [])
            ->merge($this->config('exclusions', []))
            ->filter()
            ->unique()
            ->all();

        if ($exclusions) {
            $query->whereNotIn('id', $exclusions);
        }

        $this->applyIndexQueryScopes($query, $request->all());

        return $query;
    }

    public function taxonomies()
    {
        $taxonomy = $this->config('taxonomy');
        $taxonomies = $this->config('taxonomies');

        if ($taxonomy && $taxonomies) {
            throw new TermsFieldtypeBothOptionsUsedException;
        }

        if ($taxonomy && ! $taxonomies) {
            throw new TermsFieldtypeTaxonomyOptionUsed;
        }

        return Arr::wrap($taxonomies);
    }

    public function usingSingleTaxonomy()
    {
        return count($this->taxonomies()) === 1;
    }

    protected function createTermFromString($string, $taxonomy)
    {
        $slug = Str::slug($string, '-', $this->termLang());

        // An existing term matching the full string wins over path parsing. This lets a term
        // whose title contains the delimiter (e.g. "Ages > 21", created through the CP term
        // form) be matched by typing it, instead of always being split into a path. The
        // trade-off is that typing a path like "animals > cat" could match an unrelated
        // existing term (e.g. "animalscat") instead of creating the nested path — accepted
        // as low-probability.
        if ($term = Facades\Term::find("{$taxonomy}::{$slug}")) {
            return $term->id();
        }

        if (Str::contains($string, EnsuresTermPaths::DELIMITER)
            && ($hierarchical = Facades\Taxonomy::findByHandle($taxonomy))
            && $hierarchical->hierarchical()) {
            return $this->createTermsFromPath($string, $hierarchical);
        }

        $taxonomy = Facades\Taxonomy::findByHandle($taxonomy);

        if (User::current()->cant('create', [TermContract::class, $taxonomy])) {
            return null;
        }

        $term = Facades\Term::make()
            ->slug($slug)
            ->taxonomy($taxonomy)
            ->set('title', $string);

        $term->save();

        return $term->id();
    }

    /**
     * A typed value like "animals > cat > calico" on a hierarchical taxonomy creates
     * each missing segment as a term chained under the previous one, and returns
     * the leaf's id. Existing segments are reused in place — the fieldtype
     * never re-parents a term that's already somewhere in the tree.
     */
    private function createTermsFromPath(string $path, $taxonomy)
    {
        $segments = collect(explode(EnsuresTermPaths::DELIMITER, $path))
            ->map(fn ($segment) => trim($segment))
            ->filter()
            ->values();

        if ($segments->isEmpty()) {
            return null;
        }

        $maxDepth = $taxonomy->structure()->maxDepth();

        if ($maxDepth && $segments->count() > $maxDepth) {
            throw ValidationException::withMessages([
                $this->field->handle() => __('statamic::validation.term_path_exceeds_max_depth', [
                    'path' => $path,
                    'max' => $maxDepth,
                ]),
            ]);
        }

        $slug = (new EnsuresTermPaths)->ensure(
            $taxonomy,
            $path,
            $this->termLang(),
            fn () => User::current()->can('create', [TermContract::class, $taxonomy])
        );

        return $slug ? $taxonomy->handle().'::'.$slug : null;
    }

    private function termLang()
    {
        // The parent is the item this terms fieldtype exists on. Most commonly an
        // entry, but could also be something else, like another taxonomy term.
        $parent = $this->field->parent();

        return $parent instanceof Localization
            ? Site::get($parent->locale())->lang()
            : Site::default()->lang();
    }

    protected function getConfiguredTaxonomies()
    {
        return empty($taxonomies = $this->config('taxonomies'))
            ? Taxonomy::handles()->all()
            : $taxonomies;
    }

    public function toGqlType()
    {
        $type = GraphQL::type(TermInterface::NAME);

        if ($this->config('max_items') !== 1) {
            $type = GraphQL::listOf($type);
        }

        return $type;
    }

    protected function getItemsForPreProcessIndex($values): Collection
    {
        if (! $augmented = $this->augment($values)) {
            return collect();
        }

        return $this->config('max_items') === 1 ? collect([$augmented]) : $augmented->get();
    }

    public function relationshipQueryBuilder()
    {
        $taxonomies = $this->taxonomies();

        return Term::query()
            ->when($taxonomies, fn ($query) => $query->whereIn('taxonomy', $taxonomies));
    }

    public function relationshipQueryIdMapFn(): ?\Closure
    {
        return $this->usingSingleTaxonomy()
            ? fn ($term) => Str::after($term->id(), '::')
            : null;
    }

    public function getItemHint($item): ?string
    {
        return collect([
            count($this->getConfiguredTaxonomies()) > 1 ? __($item->taxonomy()->title()) : null,
            $item->taxonomy()?->hierarchical() ? $item->ancestors()->map->title()->implode(' » ') : null,
        ])->filter()->implode(' • ');
    }

    public function replaceTermReferences($data, ?string $newValue, string $oldValue, string $taxonomy)
    {
        $configuredTaxonomies = Arr::wrap($this->config('taxonomies'));

        if (count($configuredTaxonomies) > 0) {
            if (! in_array($taxonomy, $configuredTaxonomies)) {
                return $data;
            }

            return is_string($data)
                ? $this->replaceValue($data, $newValue, $oldValue, $taxonomy)
                : $this->replaceValuesInArray($data, $newValue, $oldValue, $taxonomy);
        }

        $scopedOldValue = "{$taxonomy}::{$oldValue}";
        $scopedNewValue = $newValue !== null ? "{$taxonomy}::{$newValue}" : null;

        return is_string($data)
            ? $this->replaceValue($data, $scopedNewValue, $scopedOldValue, $taxonomy)
            : $this->replaceValuesInArray($data, $scopedNewValue, $scopedOldValue, $taxonomy);
    }

    protected function replaceValue($data, $newValue, $oldValue, string $taxonomy)
    {
        if (! $this->valueRefersToTerm($data, $oldValue, $taxonomy)) {
            return $data;
        }

        if ($newValue === null) {
            return null;
        }

        return $this->rewriteTermValue($data, $oldValue, $newValue, $taxonomy);
    }

    protected function replaceValuesInArray($data, $newValue, $oldValue, string $taxonomy)
    {
        if (! is_array($data) || ! $data) {
            return $data;
        }

        $result = collect(Arr::dot($data))
            ->map(fn ($value) => $this->valueRefersToTerm($value, $oldValue, $taxonomy)
                ? ($newValue === null ? null : $this->rewriteTermValue($value, $oldValue, $newValue, $taxonomy))
                : $value)
            ->filter()
            ->values();

        return $result->isEmpty() ? null : $result->all();
    }

    private function valueRefersToTerm($value, $oldValue, string $taxonomyHandle): bool
    {
        if ($value === $oldValue) {
            return true;
        }

        if (! is_string($value) || ! is_string($oldValue)) {
            return false;
        }

        [$path, $taxonomy] = $this->termPathAndTaxonomy($value);
        [$oldSlug, $oldTaxonomy] = $this->termPathAndTaxonomy($oldValue);

        if ($oldTaxonomy && $taxonomy !== $oldTaxonomy) {
            return false;
        }

        $handle = $taxonomy ?? $oldTaxonomy ?? $taxonomyHandle;

        if (! $this->taxonomyIsHierarchical($handle)) {
            return Str::slug($path) === $oldSlug;
        }

        return collect(explode(EnsuresTermPaths::DELIMITER, $path))->contains(
            fn ($segment) => $segment === $oldSlug || Str::slug($segment) === $oldSlug
        );
    }

    private function rewriteTermValue(string $value, string $oldValue, string $newValue, string $taxonomyHandle): string
    {
        if ($value === $oldValue) {
            return $newValue;
        }

        [$path, $taxonomy] = $this->termPathAndTaxonomy($value);
        [$oldSlug] = $this->termPathAndTaxonomy($oldValue);
        [$newSlug, $newTaxonomy] = $this->termPathAndTaxonomy($newValue);

        $handle = $taxonomy ?? $newTaxonomy ?? $taxonomyHandle;
        $prefix = $taxonomy ?? $newTaxonomy;

        if (! $this->taxonomyIsHierarchical($handle)) {
            return $prefix ? $prefix.'::'.$newSlug : $newValue;
        }

        $rewritten = collect(explode(EnsuresTermPaths::DELIMITER, $path))
            ->map(fn ($segment) => $segment === $oldSlug || Str::slug($segment) === $oldSlug ? $newSlug : $segment)
            ->implode(EnsuresTermPaths::DELIMITER);

        return $prefix ? $prefix.'::'.$rewritten : $rewritten;
    }

    private function taxonomyIsHierarchical(?string $handle): bool
    {
        return $handle && Taxonomy::findByHandle($handle)?->hierarchical();
    }

    private function termPathAndTaxonomy(string $value): array
    {
        if (str_contains($value, '::')) {
            return [Str::after($value, '::'), Str::before($value, '::')];
        }

        return [$value, null];
    }
}
