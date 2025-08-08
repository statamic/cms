<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Facades\Action;
use Statamic\Facades\Site;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Http\Resources\CP\Taxonomies\Term as TermResource;
use Statamic\Http\Resources\CP\Taxonomies\Terms;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Statamic\Rules\Slug;
use Statamic\Rules\UniqueTermValue;

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

        $sortField = request('sort');
        $sortDirection = request('order', 'asc');

        if (! $sortField && ! request('search')) {
            $sortField = $taxonomy->sortField();
            $sortDirection = $taxonomy->sortDirection();
        }

        if ($sortField) {
            $query->orderBy($sortField, $sortDirection);
        }

        $paginator = $query->paginate(request('perPage'));

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

        $term = $term->fromWorkingCopy();

        $blueprint = $term->blueprint();

        [$values, $meta] = $this->extractFromFields($term, $blueprint);

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
                'revisions' => $term->revisionsUrl(),
                'restore' => $term->restoreRevisionUrl(),
                'createRevision' => $term->createRevisionUrl(),
                'editBlueprint' => cp_route('blueprints.taxonomies.edit', [$taxonomy, $blueprint]),
            ],
            'values' => array_merge($values, ['id' => $term->id()]),
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
            })->all(),
            'hasWorkingCopy' => $term->hasWorkingCopy(),
            'revisionsEnabled' => $term->revisionsEnabled(),
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

        return view('statamic::terms.edit', array_merge($viewData, [
            'term' => $term,
        ]));
    }

    public function update(Request $request, $taxonomy, $term, $site)
    {
        $term = $term->in($site->handle());

        $this->authorize('update', $term);

        $term = $term->fromWorkingCopy();

        $fields = $term->blueprint()->fields()->addValues($request->except('id'));

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

        $values = $values->except(['slug', 'date']);

        if ($term->hasOrigin()) {
            $term->data($values->only($request->input('_localized')));
        } else {
            $term->merge($values);
        }

        $term->slug($request->slug);

        if ($term->revisionsEnabled() && $term->published()) {
            $term
                ->makeWorkingCopy()
                ->user(User::current())
                ->save();
        } else {
            if (! $term->revisionsEnabled()) {
                $term->published($request->published);
            }

            $saved = $term->updateLastModified(User::current())->save();
        }

        [$values] = $this->extractFromFields($term, $term->blueprint());

        return (new TermResource($term))
            ->additional([
                'saved' => $saved,
                'data' => [
                    'values' => $values,
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

        $fields = $blueprint
            ->fields()
            ->preProcess();

        $values = $fields->values()->merge([
            'title' => null,
            'slug' => null,
            'published' => $taxonomy->defaultPublishState(),
        ]);

        $viewData = [
            'title' => $taxonomy->createLabel(),
            'actions' => [
                'save' => cp_route('taxonomies.terms.store', [$taxonomy->handle(), $site->handle()]),
                'editBlueprint' => cp_route('blueprints.taxonomies.edit', [$taxonomy, $blueprint]),
            ],
            'values' => $values,
            'meta' => $fields->meta(),
            'taxonomy' => $taxonomy->handle(),
            'taxonomyCreateLabel' => $taxonomy->createLabel(),
            'blueprint' => $blueprint->toPublishArray(),
            'published' => $taxonomy->defaultPublishState(),
            'locale' => $site->handle(),
            'localizations' => $this->getAuthorizedSitesForTaxonomy($taxonomy)->map(function ($handle) use ($taxonomy, $site) {
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $site->handle(),
                    'exists' => false,
                    'published' => false,
                    'url' => cp_route('taxonomies.terms.create', [$taxonomy->handle(), $handle]),
                    'livePreviewUrl' => cp_route('taxonomies.terms.preview.create', [$taxonomy->handle(), $handle]),
                ];
            })->values()->all(),
            'previewTargets' => $taxonomy->previewTargets()->all(),
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic::terms.create', $viewData);
    }

    public function store(Request $request, $taxonomy, $site)
    {
        $this->authorize('store', [TermContract::class, $taxonomy]);

        $blueprint = $taxonomy->termBlueprint($request->_blueprint);

        $fields = $blueprint->fields()->addValues($request->all());

        $fields->validate([
            'title' => 'required',
            'slug' => ['required', new UniqueTermValue(taxonomy: $taxonomy->handle(), site: $site->handle())],
        ]);

        $values = $fields->process()->values()->except(['slug', 'blueprint']);

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

        if ($term->revisionsEnabled()) {
            $term->store([
                'message' => $request->message,
                'user' => User::current(),
            ]);
        } else {
            $saved = $term->updateLastModified(User::current())->save();
        }

        return (new TermResource($term))
            ->additional(['saved' => $saved]);
    }

    protected function getAuthorizedSitesForTaxonomy($taxonomy)
    {
        return $taxonomy
            ->sites()
            ->filter(fn ($handle) => User::current()->can('view', Site::get($handle)));
    }
}
