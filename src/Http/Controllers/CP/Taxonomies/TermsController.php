<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Facades\Action;
use Statamic\Facades\Site;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Http\Resources\CP\Taxonomies\Term as TermResource;
use Statamic\Http\Resources\CP\Taxonomies\Terms;
use Statamic\Query\OrderBy;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Statamic\Rules\Slug;
use Statamic\Rules\UniqueTermValue;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\Str;

use function Statamic\trans as __;

class TermsController extends CpController
{
    use ExtractsFromTermFields,
        QueriesFilters;

    public function index(FilteredRequest $request, $taxonomy)
    {
        $this->authorize('view', $taxonomy);

        $query = $this->indexQuery($taxonomy);

        $activeFilterBadges = $this->queryFilters($query, $request->filters, [
            'taxonomy' => $taxonomy->handle(),
            'blueprints' => $taxonomy->termBlueprints()->map->handle(),
        ]);

        $sortField = OrderBy::column(request('sort'));
        $sortDirection = request('order', 'asc');

        if (! $sortField && ! request('search')) {
            $sortField = $taxonomy->sortField();
            $sortDirection = $taxonomy->sortDirection();
        }

        if ($sortField) {
            $query->orderBy($sortField, $sortDirection);
        }

        $paginator = $query->paginate(Statamic::cpPerPage(request('perPage')));

        $terms = $paginator->getCollection();

        if (request('search') && $taxonomy->hasSearchIndex()) {
            $terms = $terms->map->getSearchable();
        }

        $terms = $terms->map->in(Site::selected()->handle());

        $paginator->setCollection($terms);

        return (new Terms($paginator))
            ->blueprint($taxonomy->termBlueprint())
            ->columnPreferenceKey("taxonomies.{$taxonomy->handle()}.columns")
            ->additional(['meta' => [
                'activeFilterBadges' => $activeFilterBadges,
            ]]);
    }

    protected function indexQuery($taxonomy)
    {
        $query = $taxonomy->queryTerms();

        $query->where('site', Site::selected());

        if ($search = request('search')) {
            if ($taxonomy->hasSearchIndex()) {
                return $taxonomy->searchIndex()->ensureExists()->search($search);
            }

            $query->where('title', 'like', '%'.$search.'%');
        }

        return $query;
    }

    public function edit(Request $request, $taxonomy, $term)
    {
        $this->authorize('view', $term);

        $blueprint = $term->blueprint();

        [$values, $meta, $extraValues, $blueprint] = $this->extractFromFields($term, $blueprint);

        if ($hasOrigin = $term->hasOrigin()) {
            [$originValues, $originMeta] = $this->extractFromFields($term->origin(), $blueprint);
        }

        $viewData = [
            'title' => $term->value('title'),
            'reference' => $term->reference(),
            'editing' => true,
            'actions' => [
                'save' => $term->updateUrl(),
                'publish' => $term->publishUrl(),
                'unpublish' => $term->unpublishUrl(),
                'editBlueprint' => cp_route('blueprints.taxonomies.edit', [$taxonomy, $blueprint]),
            ],
            'values' => array_merge($values, ['id' => $term->id()]),
            'extraValues' => $extraValues,
            'meta' => $meta,
            'taxonomy' => $taxonomy->handle(),
            'blueprint' => $blueprint->toPublishArray(),
            'readOnly' => User::current()->cant('edit', $term),
            'published' => $term->published(),
            'locale' => $term->locale(),
            'localizedFields' => $term->data()->keys()->all(),
            'hasOrigin' => $hasOrigin,
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
            'permalink' => $term->absoluteUrl(),
            'localizations' => $this->getAuthorizedSitesForTaxonomy($taxonomy)->map(function ($handle) use ($term) {
                $localized = $term->in($handle);

                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $term->locale(),
                    'exists' => true,
                    'root' => $localized->isRoot(),
                    'origin' => $localized->isRoot(),
                    'published' => $localized->published(),
                    'url' => $localized->editUrl(),
                    'livePreviewUrl' => $localized->livePreviewUrl(),
                ];
            })->values()->all(),
            'previewTargets' => $taxonomy->previewTargets()->all(),
            'itemActions' => Action::for($term, ['taxonomy' => $taxonomy->handle(), 'view' => 'form']),
            'hasTemplate' => view()->exists($term->template()),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        if ($request->has('created')) {
            session()->now('success', __('Term created'));
        }

        return Inertia::render('terms/Edit', [
            ...$viewData,
            'canEditBlueprint' => User::current()->can('configure fields'),
            'createAnotherUrl' => cp_route('taxonomies.terms.create', [$taxonomy->handle(), $term->locale()]),
            'listingUrl' => cp_route('taxonomies.show', $taxonomy->handle()),
            'itemActionUrl' => cp_route('taxonomies.terms.actions.run', $taxonomy->handle()),
        ]);
    }

    public function update(Request $request, $taxonomy, $term, $site)
    {
        $term = $term->in($site->handle());

        $this->authorize('update', $term);

        $term->term()->syncOriginal();

        $fields = $taxonomy
            ->ensurePublishParentField($term->blueprint(), $term)
            ->fields()
            ->addValues($request->except('id'));

        $fields->validate([
            'title' => 'required',
            'slug' => [
                'required',
                new Slug,
                new UniqueTermValue(taxonomy: $taxonomy->handle(), except: $term->id(), site: $site->handle()),
            ],
        ]);

        $values = $fields->process()->values();

        if ($explicitBlueprint = $values->pull('blueprint')) {
            $term->blueprint($explicitBlueprint);
        }

        $parent = $taxonomy->hierarchical() ? $values->pull('parent') : null;

        if ($taxonomy->hierarchical() && $request->exists('parent')) {
            $this->assertValidParentMove($taxonomy, $term, $parent);
        }

        $values = $values->except(['slug', 'date']);

        if ($term->hasOrigin()) {
            $term->data($values->only($request->input('_localized')));
        } else {
            $term->merge($values);
        }

        $term->slug($request->slug);

        $term->published($request->published);

        $saved = $term->updateLastModified(User::current())->save();

        if ($taxonomy->hierarchical() && $request->exists('parent')) {
            $this->moveTermInTree($taxonomy, $term, $parent);
        }

        [$values, $meta, $extraValues] = $this->extractFromFields($term, $term->blueprint());

        return (new TermResource($term))
            ->additional([
                'saved' => $saved,
                'data' => [
                    'values' => $values,
                    'extraValues' => $extraValues,
                ],
            ]);
    }

    public function create(Request $request, $taxonomy, $site)
    {
        $this->authorize('create', [TermContract::class, $taxonomy, $site]);

        $blueprint = $taxonomy->termBlueprint($request->blueprint);

        if (! $blueprint) {
            throw new \Exception('A valid blueprint is required.');
        }

        $blueprint = $taxonomy->ensurePublishParentField($blueprint);

        $values = [];

        if ($taxonomy->hierarchical() && $request->parent) {
            $values['parent'] = Arr::wrap($request->parent);
        }

        $fields = $blueprint
            ->fields()
            ->addValues($values)
            ->preProcess();

        $values = $fields->values()->merge([
            'title' => null,
            'slug' => null,
            'published' => $taxonomy->defaultPublishState(),
        ]);

        $extraValues = [
            'depth' => 1,
            'children' => [],
        ];

        if ($taxonomy->hierarchical() && $request->parent) {
            $parentTerm = Term::find($taxonomy->handle().'::'.$this->termSlugFromParentValue($taxonomy, $request->parent))?->in($site->handle());
            $extraValues['depth'] = ($parentTerm?->depth() ?? 0) + 1;
        }

        $viewData = [
            'title' => $taxonomy->createLabel(),
            'actions' => [
                'save' => cp_route('taxonomies.terms.store', [$taxonomy->handle(), $site->handle()]),
                'editBlueprint' => cp_route('blueprints.taxonomies.edit', [$taxonomy, $blueprint]),
            ],
            'values' => $values,
            'extraValues' => $extraValues,
            'meta' => $fields->meta(),
            'taxonomy' => $taxonomy->handle(),
            'taxonomyCreateLabel' => $taxonomy->createLabel(),
            'parent' => $taxonomy->hasStructure() ? $request->parent : null,
            'blueprint' => $blueprint->toPublishArray(),
            'published' => $taxonomy->defaultPublishState(),
            'locale' => $site->handle(),
            'localizations' => $this->getAuthorizedSitesForTaxonomy($taxonomy)->map(function ($handle) use ($taxonomy, $site, $request) {
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $site->handle(),
                    'exists' => false,
                    'published' => false,
                    'url' => cp_route('taxonomies.terms.create', [$taxonomy->handle(), $handle, 'blueprint' => $request->blueprint, 'parent' => $request->parent]),
                    'livePreviewUrl' => cp_route('taxonomies.terms.preview.create', [$taxonomy->handle(), $handle]),
                ];
            })->values()->all(),
            'previewTargets' => $taxonomy->previewTargets()->all(),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return Inertia::render('terms/Create', [
            ...$viewData,
            'canEditBlueprint' => User::current()->can('configure fields'),
            'createAnotherUrl' => cp_route('taxonomies.terms.create', [$taxonomy->handle(), $site->handle(), 'blueprint' => $request->blueprint, 'parent' => $request->parent]),
            'listingUrl' => cp_route('taxonomies.show', $taxonomy->handle()),
        ]);
    }

    public function store(Request $request, $taxonomy, $site)
    {
        $this->authorize('store', [TermContract::class, $taxonomy]);

        $blueprint = $taxonomy->ensurePublishParentField($taxonomy->termBlueprint($request->_blueprint));

        $fields = $blueprint->fields()->addValues($request->all());

        $fields->validate([
            'title' => 'required',
            'slug' => ['required', new UniqueTermValue(taxonomy: $taxonomy->handle(), site: $site->handle())],
        ]);

        $values = $fields->process()->values()->except(['slug', 'blueprint']);

        $parent = $taxonomy->hierarchical()
            ? ($values->pull('parent') ?: $request->_parent)
            : null;

        $term = Term::make()
            ->taxonomy($taxonomy)
            ->blueprint($request->_blueprint)
            ->in($site->handle());

        $slug = $request->slug;
        $published = $request->get('published'); // TODO
        $defaultSite = $term->taxonomy()->sites()->first();

        // If the term is *not* being created in the default site, we'll copy all the
        // appropriate values into the default localization since it needs to exist.
        if ($site->handle() !== $defaultSite) {
            $term
                ->in($defaultSite)
                ->published($published)
                ->data($values)
                ->slug($slug);
        }

        $term
            ->published($published)
            ->data($values)
            ->slug($slug);

        if ($taxonomy->hierarchical() && $parent) {
            $this->assertValidParentMove($taxonomy, $term, $parent);
        }

        $saved = $term->updateLastModified(User::current())->save();

        if ($saved && $taxonomy->hierarchical() && $parent) {
            $this->graftTermIntoTree($taxonomy, $term, $parent);
        }

        return (new TermResource($term))
            ->additional(['saved' => $saved]);
    }

    private function graftTermIntoTree($taxonomy, $term, $parent)
    {
        $parent = $this->termSlugFromParentValue($taxonomy, $parent);

        $tree = $taxonomy->structure()->tree();

        if (! $parent || ! $tree->find($parent)) {
            return;
        }

        $slug = $term->inDefaultLocale()->slug();

        $taxonomy->structure()->graftTerm($slug, $parent);
    }

    private function assertValidParentMove($taxonomy, $term, $parent): void
    {
        $slug = $term->inDefaultLocale()->slug();
        $parent = $this->termSlugFromParentValue($taxonomy, $parent);

        if ($parent === $slug) {
            throw ValidationException::withMessages([
                'parent' => __('statamic::validation.parent_cannot_be_itself'),
            ]);
        }

        $tree = $taxonomy->structure()->tree();
        $page = $tree->find($slug);

        if ($parent) {
            $parentPage = $tree->find($parent);

            if (! $parentPage) {
                return;
            }

            $descendantIds = collect($page?->flattenedPages()?->map->id() ?? []);

            if ($descendantIds->contains($parent)) {
                throw ValidationException::withMessages([
                    'parent' => __('statamic::validation.parent_cannot_be_descendant'),
                ]);
            }
        }

        $branchHeight = 1;

        if ($page && ($descendants = $page->flattenedPages()) && $descendants->isNotEmpty()) {
            $branchHeight = $descendants->max->depth() - $page->depth() + 1;
        }

        $parentDepth = $parent ? $tree->find($parent)->depth() : 0;

        if (($max = $taxonomy->structure()->maxDepth()) && ($parentDepth + $branchHeight) > $max) {
            throw ValidationException::withMessages([
                'parent' => __('statamic::validation.parent_exceeds_max_depth'),
            ]);
        }
    }

    private function moveTermInTree($taxonomy, $term, $parent): void
    {
        $slug = $term->inDefaultLocale()->slug();
        $parent = $this->termSlugFromParentValue($taxonomy, $parent);
        $tree = $taxonomy->structure()->tree();

        $currentParentSlug = $term->parent()?->inDefaultLocale()->slug();

        if ($currentParentSlug === $parent) {
            return;
        }

        if ($parent && ! $tree->find($parent)) {
            return;
        }

        $tree->tree($tree->tree());
        $tree->move($slug, $parent)->save();
    }

    private function termSlugFromParentValue($taxonomy, $value): ?string
    {
        $value = is_array($value) ? Arr::first($value) : $value;

        if (! $value) {
            return null;
        }

        return Str::after($value, $taxonomy->handle().'::');
    }

    protected function getAuthorizedSitesForTaxonomy($taxonomy)
    {
        return $taxonomy
            ->sites()
            ->filter(fn ($handle) => User::current()->can('view', Site::get($handle)));
    }
}
