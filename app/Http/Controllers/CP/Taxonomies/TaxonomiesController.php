<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Facades\Scope;
use Statamic\CP\Column;
use Statamic\Facades\Action;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Blueprint;
use Illuminate\Http\Request;
use Statamic\Facades\Collection;
use Statamic\Fields\Validation;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Contracts\Taxonomies\Taxonomy as TaxonomyContract;

class TaxonomiesController extends CpController
{
    public function index()
    {
        $this->authorize('index', TaxonomyContract::class, 'You are not authorized to view any taxonomies.');

        $taxonomies = Taxonomy::all()->filter(function ($taxonomy) {
            return User::current()->can('view', $taxonomy);
        })->map(function ($taxonomy) {
            return [
                'id' => $taxonomy->handle(),
                'title' => $taxonomy->title(),
                'terms' => $taxonomy->queryTerms()->count(),
                'edit_url' => $taxonomy->editUrl(),
                'terms_url' => cp_route('taxonomies.show', $taxonomy->handle()),
                'deleteable' => User::current()->can('delete', $taxonomy)
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
            'term_blueprint' => 'nullable',
            'collections' => 'array',
        ]);

        $handle = $request->handle ?? snake_case($request->title);

        $taxonomy = $this->updateTaxonomy(Taxonomy::make($handle), $data);

        $taxonomy->save();

        foreach ($request->collections as $collection) {
            $collection = Collection::findByHandle($collection);
            $collection->taxonomies(
                $collection->taxonomies()->map->handle()->push($handle)->unique()->all()
            )->save();
        }

        session()->flash('success', __('Taxonomy created'));

        return [
            'redirect' => $taxonomy->showUrl()
        ];
    }

    public function edit($taxonomy)
    {
        $this->authorize('edit', $taxonomy, 'You are not authorized to edit taxonomies.');

        $values = $taxonomy->toArray();

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
        $this->authorize('update', $taxonomy, 'You are not authorized to edit taxonomies.');

        $validation = (new Validation)->fields(
            $fields = $this->editFormBlueprint()->fields()->addValues($request->all())->process()
        );

        $request->validate($validation->rules());

        $taxonomy = $this->updateTaxonomy($taxonomy, $fields->values());

        $taxonomy->save();

        return $taxonomy->toArray();
    }

    public function destroy($taxonomy)
    {
        $this->authorize('delete', $taxonomy, 'You are not authorized to delete this taxonomy.');

        $taxonomy->delete();
    }

    protected function updateTaxonomy($taxonomy, $data)
    {
        return $taxonomy
            ->title($data['title'])
            ->termBlueprint($data['term_blueprint']);
    }

    protected function editFormBlueprint()
    {
        return Blueprint::makeFromFields([
            'title' => [
                'type' => 'text',
                'validate' => 'required',
                'width' => 50,
            ],
            'handle' => [
                'type' => 'text',
                'validate' => 'required|alpha_dash',
                'width' => 50,
            ],

            'content_model' => ['type' => 'section'],
            'term_blueprint' => [
                'type' => 'blueprints',
                'instructions' => __('Terms in this taxonomy will use this blueprint.'),
                'max_items' => 1,
            ],
        ]);
    }
}
