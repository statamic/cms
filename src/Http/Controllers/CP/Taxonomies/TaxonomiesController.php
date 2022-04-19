<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
use Statamic\Contracts\Taxonomies\Taxonomy as TaxonomyContract;
use Statamic\Contracts\Taxonomies\TermRepository;
use Statamic\CP\Column;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Scope;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Stache\Repositories\TermRepository as StacheTermRepository;

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
                'terms' => $taxonomy->queryTerms()->count(),
                'edit_url' => $taxonomy->editUrl(),
                'delete_url' => $taxonomy->deleteUrl(),
                'terms_url' => cp_route('taxonomies.show', $taxonomy->handle()),
                'blueprints_url' => cp_route('taxonomies.blueprints.index', $taxonomy->handle()),
                'deleteable' => User::current()->can('delete', $taxonomy),
            ];
        })->values();

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
                    'title' => $blueprint->title(),
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
            'hasTerms' => true, // todo $taxonomy->queryTerms()->count(),
            'blueprints' => $blueprints,
            'site' => Site::selected()->handle(),
            'columns' => $columns,
            'filters' => Scope::filters('terms', [
                'taxonomy' => $taxonomy->handle(),
                'blueprints' => $blueprints->pluck('handle')->all(),
            ]),
        ];

        if ($taxonomy->queryTerms()->count() === 0) {
            return view('statamic::taxonomies.empty', $viewData);
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
            'handle' => 'nullable|alpha_dash',
        ]);

        $handle = $request->handle ?? snake_case($request->title);

        if (Taxonomy::findByHandle($handle)) {
            throw new \Exception('Taxonomy already exists');
        }

        $taxonomy = Taxonomy::make($handle)->title($request->title);

        if (Site::hasMultiple()) {
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
        ];

        $fields = ($blueprint = $this->editFormBlueprint($taxonomy))
            ->fields()
            ->addValues($values)
            ->preProcess();

        return view('statamic::taxonomies.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'taxonomy' => $taxonomy,
        ]);
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
            ->previewTargets($values['preview_targets']);

        if ($sites = array_get($values, 'sites')) {
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
                        'type' => 'html',
                        'html' => ''.
                            '<div class="text-xs">'.
                            '   <span class="mr-2">'.$taxonomy->termBlueprints()->map->title()->join(', ').'</span>'.
                            '   <a href="'.cp_route('taxonomies.blueprints.index', $taxonomy).'" class="text-blue">'.__('Edit').'</a>'.
                            '</div>',
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

        if (Site::hasMultiple()) {
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
                                    'type' => 'text',
                                ],
                            ],
                            [
                                'handle' => 'format',
                                'field' => [
                                    'type' => 'text',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return Blueprint::makeFromSections($fields);
    }
}
