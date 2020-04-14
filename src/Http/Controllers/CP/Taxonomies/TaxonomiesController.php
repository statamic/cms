<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Facades\Scope;
use Statamic\CP\Column;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Blueprint;
use Illuminate\Http\Request;
use Statamic\Facades\Collection;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Contracts\Taxonomies\Taxonomy as TaxonomyContract;

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
                'deleteable' => User::current()->can('delete', $taxonomy)
            ];
        })->values();

        return view('statamic::taxonomies.index', [
            'taxonomies' => $taxonomies,
            'columns' => [
                Column::make('title')->label(__('Title')),
                Column::make('terms')->label(__('Terms')),
            ],
        ]);
    }

    public function show($taxonomy)
    {
        $this->authorize('view', $taxonomy);

        $blueprints = $taxonomy->termBlueprints()->map(function ($blueprint) {
            return [
                'handle' => $blueprint->handle(),
                'title' => $blueprint->title(),
            ];
        });

        return view('statamic::taxonomies.show', [
            'taxonomy' => $taxonomy,
            'hasTerms' => true, // todo $taxonomy->queryTerms()->count(),
            'blueprints' => $blueprints,
            'site' => Site::selected(),
            'filters' => Scope::filters('terms', [
                'taxonomy' => $taxonomy->handle(),
                'blueprints' => $blueprints->pluck('handle')->all(),
            ]),
        ]);
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

        if (Collection::find($handle)) {
            throw new \Exception('Taxonomy already exists');
        }

        $taxonomy = Taxonomy::make($handle)->title($request->title);

        $taxonomy->save();

        session()->flash('success', __('Taxonomy created'));

        return [
            'redirect' => $taxonomy->showUrl()
        ];
    }

    public function edit($taxonomy)
    {
        $this->authorize('edit', $taxonomy, __('You are not authorized to edit this taxonomy.'));

        $values = [
            'title' => $taxonomy->title(),
            'blueprints' => $taxonomy->termBlueprints()->map->handle()->all(),
        ];

        $fields = ($blueprint = $this->editFormBlueprint())
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

        $fields = $this->editFormBlueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values()->all();

        $taxonomy
            ->title($values['title'])
            ->termBlueprints($values['blueprints']);

        $taxonomy->save();

        return $taxonomy->toArray();
    }

    public function destroy($taxonomy)
    {
        $this->authorize('delete', $taxonomy, __('You are not authorized to delete this taxonomy.'));

        $taxonomy->delete();
    }

    protected function editFormBlueprint()
    {
        return Blueprint::makeFromSections([
            'name' => [
                'display' => __('Name'),
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'validate' => 'required',
                    ],
                ]
            ],
            'content_model' => [
                'display' => __('Content Model'),
                'fields' => [
                    'blueprints' => [
                        'display' => __('Blueprints'),
                        'instructions' => __('statamic::messages.taxonomies_blueprints_instructions'),
                        'type' => 'blueprints',
                        'validate' => 'array',
                        'mode' => 'select',
                    ],
                ]
            ]
        ]);
    }
}
