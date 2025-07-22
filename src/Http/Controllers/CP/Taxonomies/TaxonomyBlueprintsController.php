<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
use Statamic\Contracts\Taxonomies\Taxonomy as TaxonomyContract;
use Statamic\CP\Breadcrumbs\Breadcrumb;
use Statamic\CP\Breadcrumbs\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Taxonomy;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Fields\ManagesBlueprints;

class TaxonomyBlueprintsController extends CpController
{
    use ManagesBlueprints;

    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function index(TaxonomyContract $taxonomy)
    {
        $this->pushTaxonomyBreadcrumbs($taxonomy);

        $blueprints = $this->indexItems($taxonomy->termBlueprints(), $taxonomy);

        return view('statamic::taxonomies.blueprints.index', compact('taxonomy', 'blueprints'));
    }

    private function editUrl($taxonomy, $blueprint)
    {
        return cp_route('blueprints.taxonomies.edit', [$taxonomy, $blueprint]);
    }

    private function deleteUrl($taxonomy, $blueprint)
    {
        return cp_route('blueprints.taxonomies.destroy', [$taxonomy, $blueprint]);
    }

    public function edit($taxonomy, $blueprint)
    {
        $blueprint = $taxonomy->termBlueprint($blueprint);

        $this->pushTaxonomyBreadcrumbs($taxonomy);

        Breadcrumbs::push(new Breadcrumb(
            text: $blueprint->title(),
            url: request()->url(),
            icon: 'taxonomies',
            links: $taxonomy
                ->termBlueprints()
                ->reject(fn ($b) => $b->handle() === $blueprint->handle())
                ->map(fn ($b) => [
                    'text' => $b->title(),
                    'icon' => 'taxonomies',
                    'url' => cp_route('blueprints.taxonomies.edit', [$taxonomy, $b]),
                ])
                ->values()
                ->all(),
            createLabel: 'Create Blueprint',
            createUrl: cp_route('blueprints.taxonomies.create', $taxonomy),
        ));

        return view('statamic::taxonomies.blueprints.edit', [
            'taxonomy' => $taxonomy,
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint),
        ]);
    }

    public function update(Request $request, $taxonomy, $blueprint)
    {
        $request->validate([
            'title' => 'required',
            'tabs' => 'array',
        ]);

        $this->updateBlueprint($request, $taxonomy->termBlueprint($blueprint));
    }

    public function create($taxonomy)
    {
        $this->pushTaxonomyBreadcrumbs($taxonomy);

        return view('statamic::taxonomies.blueprints.create', [
            'action' => cp_route('blueprints.taxonomies.store', $taxonomy),
        ]);
    }

    public function store(Request $request, $taxonomy)
    {
        $request->validate(['title' => 'required']);

        // If there are no user-defined blueprints, save the default one.
        // To the user, it would have looked like the default one existed since it's in the listing.
        // The new one the user is about to create should be considered the second one.
        if (Blueprint::in('taxonomies/'.$taxonomy->handle())->count() === 0) {
            $taxonomy->termBlueprint()->save();
        }

        $blueprint = $this->storeBlueprint($request, 'taxonomies.'.$taxonomy->handle());

        return ['redirect' => cp_route('blueprints.taxonomies.edit', [$taxonomy, $blueprint])];
    }

    public function destroy($taxonomy, $blueprint)
    {
        $blueprint = $taxonomy->termBlueprint($blueprint);

        $this->authorize('delete', $blueprint);

        $blueprint->delete();
    }

    private function pushTaxonomyBreadcrumbs(TaxonomyContract $taxonomy)
    {
        Breadcrumbs::push(new Breadcrumb(
            text: 'Taxonomies',
            icon: 'taxonomies',
        ));

        Breadcrumbs::push(new Breadcrumb(
            text: $taxonomy->title(),
            url: request()->url(),
            icon: 'taxonomies',
            links: Taxonomy::all()
                ->reject(fn ($t) => $t->handle() === $taxonomy->handle())
                ->map(fn ($t) => [
                    'text' => $t->title(),
                    'icon' => 'taxonomies',
                    'url' => cp_route('blueprints.taxonomies.index', $t),
                ])
                ->values()
                ->all(),
        ));
    }
}
