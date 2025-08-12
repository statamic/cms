<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
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

        if ($taxonomies->isEmpty()) {
            return view('statamic::taxonomies.empty');
        }

        return view('statamic::taxonomies.index', [
            'taxonomies' => $taxonomies,
            'columns' => [
                Column::make('title')->label(__('Title')),
                Column::make('terms')->label(__('Terms'))->numeric(true),
            ],
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

        $viewData = [
            'taxonomy' => $taxonomy,
            'blueprints' => $blueprints,
            'site' => Site::selected()->handle(),
            'columns' => $columns,
            'filters' => Scope::filters('terms', [
                'taxonomy' => $taxonomy->handle(),
                'blueprints' => $blueprints->pluck('handle')->all(),
            ]),
            'canCreate' => User::current()->can('create', [TermContract::class, $taxonomy]) && $taxonomy->hasVisibleTermBlueprint(),
        ];

        if ($taxonomy->queryTerms()->count() === 0) {
            return view('statamic::terms.empty', $viewData);
        }

        return view('statamic::taxonomies.show', $viewData);
    }

    public function create()
    {
        $this->authorize('create', TaxonomyContract::class, __('You are not authorized to create taxonomies.'));

        return view('statamic::taxonomies.create');
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

        $taxonomy
            ->title($values['title'])
            ->previewTargets($values['preview_targets'])
            ->termTemplate($values['term_template'] ?? null)
            ->template($values['template'] ?? null)
            ->layout($values['layout'] ?? null);

        if ($sites = Arr::get($values, 'sites')) {
            $taxonomy->sites($sites);
        }

        $taxonomy->save();

        $this->clearStacheStore($taxonomy, $existingSites);

        $this->associateTaxonomyWithCollections($taxonomy, $values['collections']);

        return $taxonomy->toArray();
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
            'routing' => [
                'display' => __('Routing & URLs'),
                'fields' => [
                    'preview_targets' => [
                        'display' => __('Preview Targets'),
                        'instructions' => __('statamic::messages.taxonomies_preview_targets_instructions'),
                        'type' => 'grid',
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
                    ],
                    'term_template' => [
                        'display' => __('Term Template'),
                        'instructions' => __('statamic::messages.taxonomy_configure_term_template_instructions'),
                        'type' => 'template',
                        'placeholder' => __('System default'),
                    ],
                    'layout' => [
                        'display' => __('Layout'),
                        'instructions' => __('statamic::messages.taxonomy_configure_layout_instructions'),
                        'type' => 'template',
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
}
