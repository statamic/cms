<?php

namespace Statamic\Fieldtypes;

use Illuminate\Support\Collection;
use Statamic\Contracts\Data\Localization;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\CP\Column;
use Statamic\Exceptions\TaxonomyNotFoundException;
use Statamic\Exceptions\TermsFieldtypeBothOptionsUsedException;
use Statamic\Exceptions\TermsFieldtypeTaxonomyOptionUsed;
use Statamic\Facades;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\GraphQL\Types\TermInterface;
use Statamic\Http\Resources\CP\Taxonomies\Terms as TermsResource;
use Statamic\Query\OrderedQueryBuilder;
use Statamic\Query\Scopes\Filters\Fields\Terms as TermsFilter;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Taxonomies\LocalizedTerm;

class Terms extends Relationship
{
    protected $canEdit = true;
    protected $canCreate = true;
    protected $canSearch = true;
    protected $statusIcons = false;
    protected $taggable = true;
    protected $icon = 'taxonomy';
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
        'initialIsRoot' => 'isRoot',
        'initialReadOnly' => 'readOnly',
        'revisionsEnabled' => 'revisionsEnabled',
        'breadcrumbs' => 'breadcrumbs',
        'taxonomyHandle' => 'taxonomy',
    ];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'max_items' => [
                        'display' => __('Max Items'),
                        'instructions' => __('statamic::messages.max_items_instructions'),
                        'min' => 1,
                        'type' => 'integer',
                    ],
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
                    'create' => [
                        'display' => __('Allow Creating'),
                        'instructions' => __('statamic::fieldtypes.terms.config.create'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                    'taxonomies' => [
                        'display' => __('Taxonomies'),
                        'instructions' => __('statamic::fieldtypes.terms.config.taxonomies'),
                        'type' => 'taxonomies',
                        'mode' => 'select',
                    ],
                    'query_scopes' => [
                        'display' => __('Query Scopes'),
                        'instructions' => __('statamic::fieldtypes.terms.config.query_scopes'),
                        'type' => 'taggable',
                    ],
                ],
            ],
        ];
    }

    public function filter()
    {
        return new TermsFilter($this);
    }

    public function augment($values)
    {
        // The parent is the item this terms fieldtype exists on. Most commonly an
        // entry, but could also be something else, like another taxonomy term.
        $parent = $this->field->parent();

        $site = $parent && ($parent instanceof Localization || $parent instanceof LocalizedTerm)
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

        return $this->config('max_items') === 1 ? $query->first() : $query;
    }

    private function convertAugmentationValuesToIds($values)
    {
        $taxonomy = $this->usingSingleTaxonomy()
            ? $this->taxonomies()[0]
            : null;

        return collect(Arr::wrap($values))->map(function ($value) use ($taxonomy) {
            if ($taxonomy) {
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

                return explode('::', $id, 2)[1];
            })
                ->unique()
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

        $query = $this->getIndexQuery($request);

        if ($sort = $this->getSortColumn($request)) {
            $query->orderBy($sort, $this->getSortDirection($request));
        }

        return $request->boolean('paginate', true) ? $query->paginate() : $query->get();
    }

    public function getResourceCollection($request, $items)
    {
        return (new TermsResource($items))
            ->blueprint($this->getBlueprint($request))
            ->columnPreferenceKey("taxonomies.{$this->getFirstTaxonomyFromRequest($request)->handle()}.columns");
    }

    protected function getBlueprint($request)
    {
        return $this->getFirstTaxonomyFromRequest($request)->termBlueprint();
    }

    protected function getFirstTaxonomyFromRequest($request)
    {
        return $request->taxonomies
            ? Facades\Taxonomy::findByHandle($request->taxonomies[0])
            : Facades\Taxonomy::all()->first();
    }

    public function getSortColumn($request)
    {
        $column = $request->get('sort');

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

        return collect($taxonomies)->flatMap(function ($taxonomyHandle) use ($taxonomies, $user) {
            $taxonomy = Taxonomy::findByHandle($taxonomyHandle);

            throw_if(! $taxonomy, new TaxonomyNotFoundException($taxonomyHandle));

            if (! $user->can('create', [TermContract::class, $taxonomy])) {
                return null;
            }

            $blueprints = $taxonomy->termBlueprints();

            return $blueprints->map(function ($blueprint) use ($taxonomy, $taxonomies, $blueprints) {
                return [
                    'title' => $this->getCreatableTitle($taxonomy, $blueprint, count($taxonomies), $blueprints->count()),
                    'url' => cp_route('taxonomies.terms.create', [$taxonomy->handle(), Site::selected()->handle()]).'?blueprint='.$blueprint->handle(),
                ];
            });
        })->all();
    }

    private function getCreatableTitle($taxonomy, $blueprint, $taxonomyCount, $blueprintCount)
    {
        if ($taxonomyCount > 1 && $blueprintCount === 1) {
            return $taxonomy->title();
        }

        if ($taxonomyCount > 1 && $blueprintCount > 1) {
            return $taxonomy->title().': '.$blueprint->title();
        }

        return $blueprint->title();
    }

    protected function toItemArray($id)
    {
        if ($this->usingSingleTaxonomy() && ! Str::contains($id, '::')) {
            $id = "{$this->taxonomies()[0]}::{$id}";
        }

        if (! $term = Term::find($id)) {
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
        ];
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

        if ($taxonomies = $request->taxonomies) {
            $query->whereIn('taxonomy', $taxonomies);
        }

        if ($search = $request->search) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        if ($site = $request->site) {
            $query->where('site', $site);
        }

        if ($request->exclusions) {
            $query->whereNotIn('id', $request->exclusions);
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
        // The parent is the item this terms fieldtype exists on. Most commonly an
        // entry, but could also be something else, like another taxonomy term.
        $parent = $this->field->parent();

        $lang = $parent instanceof Localization
            ? Site::get($parent->locale())->lang()
            : Site::default()->lang();

        $slug = Str::slug($string, '-', $lang);

        if (! $term = Facades\Term::find("{$taxonomy}::{$slug}")) {
            $term = Facades\Term::make()
                ->slug($slug)
                ->taxonomy(Facades\Taxonomy::findByHandle($taxonomy))
                ->set('title', $string);

            $term->save();
        }

        return $term->id();
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
}
