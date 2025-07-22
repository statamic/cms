<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\CP\Breadcrumbs\Breadcrumb;
use Statamic\CP\Breadcrumbs\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Fields\ManagesBlueprints;

class CollectionBlueprintsController extends CpController
{
    use ManagesBlueprints;

    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function index(CollectionContract $collection)
    {
        $this->pushCollectionBreadcrumbs($collection);

        $blueprints = $this->indexItems($collection->entryBlueprints(), $collection);

        return view('statamic::collections.blueprints.index', compact('collection', 'blueprints'));
    }

    private function editUrl($collection, $blueprint)
    {
        return cp_route('blueprints.collections.edit', [$collection, $blueprint]);
    }

    private function deleteUrl($collection, $blueprint)
    {
        return cp_route('blueprints.collections.destroy', [$collection, $blueprint]);
    }

    public function edit($collection, $blueprint)
    {
        $blueprint = $collection->entryBlueprint($blueprint);

        $this->pushCollectionBreadcrumbs($collection);

        Breadcrumbs::push(new Breadcrumb(
            text: $blueprint->title(),
            url: request()->url(),
            icon: 'collections',
            links: $collection
                ->entryBlueprints()
                ->reject(fn ($b) => $b->handle() === $blueprint->handle())
                ->map(fn ($b) => [
                    'text' => $b->title(),
                    'icon' => 'collections',
                    'url' => cp_route('blueprints.collections.edit', [$collection, $b]),
                ])
                ->values()
                ->all(),
            createLabel: 'Create Blueprint',
            createUrl: cp_route('blueprints.collections.create', $collection),
        ));

        return view('statamic::collections.blueprints.edit', [
            'collection' => $collection,
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint),
        ]);
    }

    public function update(Request $request, $collection, $blueprint)
    {
        $request->validate([
            'title' => 'required',
            'tabs' => 'array',
        ]);

        $this->updateBlueprint($request, $collection->entryBlueprint($blueprint));
    }

    public function create($collection)
    {
        $this->pushCollectionBreadcrumbs($collection);

        return view('statamic::collections.blueprints.create', [
            'action' => cp_route('blueprints.collections.store', $collection),
        ]);
    }

    public function store(Request $request, $collection)
    {
        $request->validate(['title' => 'required']);

        // If there are no user-defined blueprints, save the default one.
        // To the user, it would have looked like the default one existed since it's in the listing.
        // The new one the user is about to create should be considered the second one.
        if (Blueprint::in('collections/'.$collection->handle())->count() === 0) {
            $collection->entryBlueprint()->save();
        }

        $blueprint = $this->storeBlueprint($request, 'collections.'.$collection->handle());

        return ['redirect' => cp_route('blueprints.collections.edit', [$collection, $blueprint])];
    }

    public function destroy($collection, $blueprint)
    {
        $blueprint = $collection->entryBlueprint($blueprint);

        $this->authorize('delete', $blueprint);

        $blueprint->delete();
    }

    private function pushCollectionBreadcrumbs(CollectionContract $collection)
    {
        Breadcrumbs::push(new Breadcrumb(
            text: 'Collections',
            icon: 'collections',
        ));

        Breadcrumbs::push(new Breadcrumb(
            text: $collection->title(),
            url: request()->url(),
            icon: 'collections',
            links: Collection::all()
                ->reject(fn ($c) => $c->handle() === $collection->handle())
                ->map(fn ($c) => [
                    'text' => $c->title(),
                    'icon' => 'collections',
                    'url' => cp_route('blueprints.collections.index', $c),
                ])
                ->values()
                ->all(),
        ));
    }
}
