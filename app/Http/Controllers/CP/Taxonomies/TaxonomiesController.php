<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Statamic\API\Site;
use Statamic\API\Scope;
use Statamic\CP\Column;
use Statamic\API\Action;
use Statamic\API\Taxonomy;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Contracts\Data\Taxonomies\Taxonomy as TaxonomyContract;

class TaxonomiesController extends CpController
{
    public function index()
    {
        $this->authorize('index', TaxonomyContract::class, 'You are not authorized to view any taxonomies.');

        $taxonomies = Taxonomy::all()->filter(function ($taxonomy) {
            return request()->user()->can('view', $taxonomy);
        })->map(function ($taxonomy) {
            return [
                'id' => $taxonomy->handle(),
                'title' => $taxonomy->title(),
                'terms' => 'todo', // todo
                'edit_url' => $taxonomy->editUrl(),
                'terms_url' => cp_route('taxonomies.show', $taxonomy->handle()),
                'deleteable' => me()->can('delete', $taxonomy)
            ];
        })->values();

        return view('statamic::taxonomies.index', [
            'taxonomies' => $taxonomies,
            'columns' => [
                Column::make('title'),
                Column::make('terms'),
            ],
        ]);
    }

    public function show($taxonomy)
    {
        $blueprints = collect([$taxonomy->termBlueprint()])->map(function ($blueprint) {
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
            'filters' => Scope::filters('terms', $context = [
                'taxonomy' => $taxonomy->handle(),
                'blueprints' => $blueprints->pluck('handle')->all(),
            ]),
            'actions' => Action::for('terms', $context),
        ]);
    }

    public function create()
    {
        return view('statamic::taxonomies.create');
    }

    public function store(Request $request)
    {
        $this->authorize('store', TaxonomyContract::class, 'You are not authorized to create taxonomies.');

        $data = $request->validate([
            'title' => 'required',
            'handle' => 'nullable|alpha_dash',
            'template' => 'nullable',
            'route' => 'nullable',
            'blueprint' => 'nullable',
        ]);

        $handle = $request->handle ?? snake_case($request->title);

        $taxonomy = $this->updateTaxonomy(Taxonomy::make($handle), $data);

        $taxonomy->save();

        session()->flash('success', __('Taxonomy created'));

        return [
            'redirect' => $taxonomy->showUrl()
        ];
    }

    protected function updateTaxonomy($taxonomy, $data)
    {
        return $taxonomy
            ->title($data['title'])
            ->route($data['route'])
            ->template($data['template'])
            ->termBlueprint($data['blueprint']);
    }
}
