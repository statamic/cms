<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Contracts\Taxonomies\Taxonomy as TaxonomyContract;
use Statamic\Contracts\Taxonomies\Term as TermContract;
use Statamic\Contracts\Taxonomies\TermRepository;
use Statamic\CP\Column;
use Statamic\CP\PublishForm;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Scope;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Rules\Handle;
use Statamic\Stache\Repositories\TermRepository as StacheTermRepository;
use Statamic\Structures\TaxonomyStructure;
use Statamic\Support\Arr;
use Statamic\Support\Str;

use function Statamic\trans as __;

class TaxonomiesController extends CpController
{
    public function index()
    {
        $this->authorize('index', TaxonomyContract::class);

        $taxonomies = Taxonomy::all()->filter(function ($taxonomy) {
            return User::current()->can('view', $taxonomy);
        })->map(function ($taxonomy) {
            return [
                'id' => $taxonomy->handle(),
                'title' => $taxonomy->title(),
                'terms' => $taxonomy->queryTerms()->pluck('slug')->unique()->count(),
                'edit_url' => $taxonomy->editUrl(),
                'delete_url' => $taxonomy->deleteUrl(),
                'terms_url' => cp_route('taxonomies.show', $taxonomy->handle()),
                'blueprints_url' => cp_route('blueprints.taxonomies.index', $taxonomy->handle()),
                'deleteable' => User::current()->can('delete', $taxonomy),
            ];
        })->values();

        return Inertia::render('taxonomies/Index', [
            'taxonomies' => $taxonomies->all(),
            'columns' => [
                Column::make('title')->label(__('Title')),
                Column::make('terms')->label(__('Terms'))->numeric(true),
            ],
            'canCreate' => User::current()->can('create', TaxonomyContract::class),
            'createUrl' => cp_route('taxonomies.create'),
        ]);
    }

    public function show($taxonomy)
    {
        $this->authorize('view', $taxonomy);

        $blueprints = $taxonomy
            ->termBlueprints()
            ->reject->hidden()
            ->map(function ($blueprint) {
                return [
                    'handle' => $blueprint->handle(),
                    'title' => __($blueprint->title()),
                ];
            })->values();

        $columns = $taxonomy
            ->termBlueprint()
            ->columns()
            ->setPreferred("taxonomies.{$taxonomy->handle()}.columns")
            ->rejectUnlisted()
            ->values();

        $site = $taxonomy->sites()->contains(Site::selected()->handle())
            ? Site::selected()->handle()
            : $taxonomy->sites()->first();

        $viewData = [
            'taxonomy' => $taxonomy->handle(),
            'taxonomyTitle' => $taxonomy->title(),
            'blueprints' => $blueprints,
            'site' => $site,
            'columns' => $columns,
            'filters' => Scope::filters('terms', [
                'taxonomy' => $taxonomy->handle(),
                'blueprints' => $blueprints->pluck('handle')->all(),
            ]),
            'canCreate' => User::current()->can('create', [TermContract::class, $taxonomy]) && $taxonomy->hasVisibleTermBlueprint(),
            'createUrl' => cp_route('taxonomies.terms.create', [$taxonomy->handle(), $site]),
            'reorderUrl' => cp_route('taxonomies.terms.reorder', $taxonomy->handle()),
            'taxonomyEditUrl' => cp_route('taxonomies.edit', $taxonomy->handle()),
            'taxonomyBlueprintsUrl' => cp_route('blueprints.taxonomies.index', $taxonomy),
            'canEdit' => User::current()->can('edit', $taxonomy),
            'canConfigureFields' => User::current()->can('configure fields'),
        ];

        if ($taxonomy->hasStructure()) {
            $structure = $taxonomy->structure();
            $viewData = array_merge($viewData, [
                'structured' => User::current()->can('reorder', $taxonomy),
                'structurePagesUrl' => cp_route('taxonomies.tree.index', $taxonomy->handle()),
                'structureSubmitUrl' => cp_route('taxonomies.tree.update', $taxonomy->handle()),
                'structureMaxDepth' => $structure->maxDepth() ?? PHP_FLOAT_MAX, // "Infinity"
            ]);
        }

        if ($taxonomy->queryTerms()->count() === 0) {
            return Inertia::render('taxonomies/Empty', $viewData);
        }

        return Inertia::render('taxonomies/Show', array_merge($viewData, [
            'actionUrl' => cp_route('taxonomies.terms.actions.run', $taxonomy->handle()),
            'sortColumn' => $taxonomy->sortField(),
            'sortDirection' => $taxonomy->sortDirection(),
            'canDelete' => User::current()->can('delete', $taxonomy),
            'deleteUrl' => cp_route('taxonomies.destroy', $taxonomy->handle()),
            'createLabel' => $taxonomy->createLabel(),
        ]));
    }

    public function create()
    {
        $this->authorize('create', TaxonomyContract::class, __('You are not authorized to create taxonomies.'));

        return Inertia::render('taxonomies/Create', [
            'submitUrl' => cp_route('taxonomies.store'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('store', TaxonomyContract::class, __('You are not authorized to create taxonomies.'));

        $request->validate([
            'title' => 'required',
            'handle' => ['nullable', new Handle],
        ]);

        $handle = $request->handle ?? Str::snake($request->title);

        if (Taxonomy::findByHandle($handle)) {
            throw new \Exception('Taxonomy already exists');
        }

        $taxonomy = Taxonomy::make($handle)->title($request->title);

        if (Site::multiEnabled()) {
            $taxonomy->sites([Site::default()->handle()]);
        }

        $taxonomy->save();

        session()->flash('success', __('Taxonomy created'));

        return [
            'redirect' => $taxonomy->showUrl(),
        ];
    }

    public function edit($taxonomy)
    {
        $this->authorize('edit', $taxonomy, __('You are not authorized to edit this taxonomy.'));

        $values = [
            'title' => $taxonomy->title(),
            'blueprints' => $taxonomy->termBlueprints()->map->handle()->all(),
            'collections' => $taxonomy->collections()->map->handle()->all(),
            'sites' => $taxonomy->sites()->all(),
            'preview_targets' => $taxonomy->basePreviewTargets(),
            'term_template' => $taxonomy->hasCustomTermTemplate() ? $taxonomy->termTemplate() : null,
            'template' => $taxonomy->hasCustomTemplate() ? $taxonomy->template() : null,
            'layout' => $taxonomy->layout(),
            'structured' => $taxonomy->hasStructure(),
            'max_depth' => optional($taxonomy->structure())->maxDepth(),
            'route_mode' => $this->routeModeForCp($taxonomy->routes()),
            'route' => $this->routeValueForCp($taxonomy),
        ];

        return PublishForm::make($this->editFormBlueprint($taxonomy))
            ->title(__('Configure Taxonomy'))
            ->values($values)
            ->asConfig()
            ->submittingTo(cp_route('taxonomies.update', $taxonomy->handle()));
    }

    public function update(Request $request, $taxonomy)
    {
        $this->authorize('update', $taxonomy, __('You are not authorized to edit this taxonomy.'));

        $fields = $this->editFormBlueprint($taxonomy)->fields()->addValues($request->all());

        $fields->validate();

        $existingSites = $taxonomy->sites();

        $values = $fields->process()->values()->all();

        $this->assertCustomRouteContainsSlug($values['route_mode'] ?? 'automagic', $values['route'] ?? null);

        $taxonomy
            ->title($values['title'])
            ->previewTargets($values['preview_targets'])
            ->termTemplate($values['term_template'] ?? null)
            ->template($values['template'] ?? null)
            ->layout($values['layout'] ?? null)
            ->routes($this->routesFromCp($values));

        if ($sites = Arr::get($values, 'sites')) {
            $taxonomy->sites($sites);
        }

        $wasStructured = $taxonomy->hasStructure();

        if (! Arr::get($values, 'structured')) {
            if ($structure = $taxonomy->structure()) {
                $structure->trees()->each->delete();
            }
            $taxonomy->structure(null);
        } else {
            $taxonomy->structure($this->makeStructure($taxonomy, $values['max_depth'] ?? null));
        }

        $taxonomy->save();

        if (! $wasStructured && $taxonomy->hasStructure()) {
            $this->seedStructureTree($taxonomy);
        }

        $this->clearStacheStore($taxonomy, $existingSites);

        $this->associateTaxonomyWithCollections($taxonomy, $values['collections']);

        return $taxonomy->toArray();
    }

    protected function makeStructure($taxonomy, $maxDepth)
    {
        if (! $structure = $taxonomy->structure()) {
            $structure = new TaxonomyStructure;
        }

        return $structure->maxDepth($maxDepth);
    }

    /**
     * Persist the tree file, seeded with all existing terms (which the
     * tree's read-time validation appends in current sort order).
     */
    protected function seedStructureTree($taxonomy)
    {
        $tree = $taxonomy->structure()->tree();

        $tree->tree($tree->tree())->save();
    }

    private function clearStacheStore($taxonomy, $oldSites)
    {
        // We're only interested in clearing the stache if you're using it.
        if (! app(TermRepository::class) instanceof StacheTermRepository) {
            return;
        }

        if ($oldSites === $taxonomy->sites()->all()) {
            return;
        }

        Stache::store('terms::'.$taxonomy->handle())->clear();
    }

    protected function associateTaxonomyWithCollections($taxonomy, $collections)
    {
        $collections = collect($collections);
        $existing = $taxonomy->collections()->map->handle();

        $collections->diff($existing)->each(function ($collection) use ($taxonomy) {
            $collection = Collection::findByHandle($collection);
            $collection->taxonomies(
                $collection->taxonomies()->map->handle()
                    ->push($taxonomy->handle())
                    ->unique()->all()
            );
            $collection->save();
        });

        $existing->diff($collections)->each(function ($collection) use ($taxonomy) {
            $collection = Collection::findByHandle($collection);
            $collection->taxonomies(
                $collection->taxonomies()->map->handle()
                    ->diff([$taxonomy->handle()])
                    ->values()->all()
            );
            $collection->save();
        });
    }

    public function destroy($taxonomy)
    {
        $this->authorize('delete', $taxonomy, __('You are not authorized to delete this taxonomy.'));

        $taxonomy->delete();
    }

    protected function editFormBlueprint($taxonomy)
    {
        $fields = [
            'name' => [
                'display' => __('Name'),
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'validate' => 'required',
                    ],
                ],
            ],
            'content_model' => [
                'display' => __('Content Model'),
                'fields' => [
                    'blueprints' => [
                        'display' => __('Blueprints'),
                        'instructions' => __('statamic::messages.taxonomies_blueprints_instructions'),
                        'type' => 'blueprints',
                        'options' => $taxonomy->termBlueprints()->map(fn ($bp) => [
                            'handle' => $bp->handle(),
                            'title' => __($bp->title()),
                            'edit_url' => cp_route('blueprints.taxonomies.edit', [$taxonomy->handle(), $bp->handle()]),
                        ])->values()->all(),
                        'all_blueprints_url' => cp_route('blueprints.taxonomies.index', $taxonomy->handle()),
                    ],
                    'collections' => [
                        'display' => __('Collections'),
                        'instructions' => __('statamic::messages.taxonomies_collections_instructions'),
                        'type' => 'collections',
                        'mode' => 'select',
                    ],
                ],
            ],
        ];

        if (Site::multiEnabled()) {
            $fields['sites'] = [
                'display' => __('Sites'),
                'fields' => [
                    'sites' => [
                        'type' => 'sites',
                        'mode' => 'select',
                        'required' => true,
                    ],
                ],
            ];
        }

        $fields = array_merge($fields, [
            'hierarchy' => [
                'display' => __('Ordering & Hierarchy'),
                'fields' => [
                    'structured' => [
                        'display' => __('Orderable'),
                        'instructions' => __('statamic::messages.taxonomies_orderable_instructions'),
                        'type' => 'toggle',
                    ],
                    'max_depth' => [
                        'display' => __('Max Depth'),
                        'instructions' => __('statamic::messages.taxonomies_max_depth_instructions'),
                        'type' => 'integer',
                        'validate' => 'min:0',
                        'if' => ['structured' => true],
                    ],
                ],
            ],
            'routing' => [
                'display' => __('Routing & URLs'),
                'fields' => [
                    'route_mode' => [
                        'display' => __('Routes'),
                        'instructions' => __('statamic::messages.taxonomies_routes_instructions'),
                        'type' => 'button_group',
                        'options' => [
                            'automagic' => __('Automagic'),
                            'custom' => __('Custom'),
                            'disabled' => __('Disabled'),
                        ],
                        'default' => 'automagic',
                    ],
                    'route' => [
                        'display' => __('Route'),
                        'instructions' => __('statamic::messages.taxonomies_route_instructions'),
                        'type' => 'collection_routes',
                        'if' => ['route_mode' => 'custom'],
                        'validate' => 'required_if:route_mode,custom',
                    ],
                    'preview_targets' => [
                        'display' => __('Preview Targets'),
                        'instructions' => __('statamic::messages.taxonomies_preview_targets_instructions'),
                        'type' => 'grid',
                        'full_width_setting' => true,
                        'fields' => [
                            [
                                'handle' => 'label',
                                'field' => [
                                    'display' => __('Label'),
                                    'type' => 'text',
                                ],
                            ],
                            [
                                'handle' => 'format',
                                'field' => [
                                    'display' => __('Format'),
                                    'type' => 'text',
                                ],
                            ],
                            [
                                'handle' => 'refresh',
                                'field' => [
                                    'display' => __('Refresh'),
                                    'type' => 'toggle',
                                    'instructions' => __('statamic::messages.taxonomies_preview_target_refresh_instructions'),
                                    'default' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'templates' => [
                'display' => __('Templates'),
                'fields' => [
                    'template' => [
                        'display' => __('Template'),
                        'instructions' => __('statamic::messages.taxonomy_configure_template_instructions'),
                        'type' => 'template',
                        'placeholder' => __('System default'),
                        'clearable' => true,
                    ],
                    'term_template' => [
                        'display' => __('Term Template'),
                        'instructions' => __('statamic::messages.taxonomy_configure_term_template_instructions'),
                        'type' => 'template',
                        'placeholder' => __('System default'),
                        'clearable' => true,
                    ],
                    'layout' => [
                        'display' => __('Layout'),
                        'instructions' => __('statamic::messages.taxonomy_configure_layout_instructions'),
                        'type' => 'template',
                        'clearable' => true,
                    ],
                ],
            ],
        ]);

        return Blueprint::make()->setContents(collect([
            'tabs' => [
                'main' => [
                    'sections' => collect($fields)->map(function ($section) {
                        return [
                            'display' => $section['display'],
                            'fields' => collect($section['fields'])->map(function ($field, $handle) {
                                return [
                                    'handle' => $handle,
                                    'field' => $field,
                                ];
                            })->values()->all(),
                        ];
                    })->values()->all(),
                ],
            ],
        ])->all());
    }

    private function routeModeForCp($routes): string
    {
        if ($routes === false) {
            return 'disabled';
        }

        if ($routes === null || $routes === []) {
            return 'automagic';
        }

        return 'custom';
    }

    private function routeValueForCp($taxonomy)
    {
        if ($taxonomy->hasCustomRoutes()) {
            $routes = $taxonomy->routes();

            if (is_array($routes) && collect($routes)->filter()->unique()->count() === 1) {
                return $taxonomy->termRoute($taxonomy->sites()->first());
            }

            if (is_array($routes)) {
                return collect($routes)
                    ->map(fn ($route, $site) => $taxonomy->termRoute($site))
                    ->all();
            }

            return $taxonomy->termRoute();
        }

        return $taxonomy->defaultTermRoute();
    }

    private function routesFromCp(array $values): mixed
    {
        $mode = $values['route_mode'] ?? 'automagic';

        if ($mode === 'disabled') {
            return false;
        }

        if ($mode !== 'custom') {
            return null;
        }

        return $this->emptyRouteToNull($values['route'] ?? null);
    }

    private function emptyRouteToNull($value)
    {
        if ($value === '' || $value === [] || $value === null) {
            return null;
        }

        if (is_array($value)) {
            $filtered = collect($value)
                ->map(fn ($route) => $route === '' ? null : $route)
                ->filter(fn ($route) => $route !== null);

            if ($filtered->isEmpty()) {
                return null;
            }

            return $filtered->all();
        }

        return $value;
    }

    private function assertCustomRouteContainsSlug(string $mode, $route): void
    {
        if ($mode !== 'custom') {
            return;
        }

        $routes = is_array($route) ? $route : [$route];

        foreach ($routes as $pattern) {
            if ($pattern && ! Str::contains((string) $pattern, '{slug}')) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'route' => __('statamic::validation.taxonomy_route_requires_slug'),
                ]);
            }
        }
    }
}
