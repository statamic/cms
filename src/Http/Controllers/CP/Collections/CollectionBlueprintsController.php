<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Facades\Blueprint;
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
        $blueprints = $this->indexItems($collection->entryBlueprints(), $collection);

        return view('statamic::collections.blueprints.index', compact('collection', 'blueprints'));
    }

    private function editUrl($collection, $blueprint)
    {
        return cp_route('collections.blueprints.edit', [$collection, $blueprint]);
    }

    private function deleteUrl($collection, $blueprint)
    {
        return cp_route('collections.blueprints.destroy', [$collection, $blueprint]);
    }

    public function edit($collection, $blueprint)
    {
        $blueprint = $collection->entryBlueprint($blueprint);

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
            'sections' => 'array',
        ]);

        $this->updateBlueprint($request, $collection->entryBlueprint($blueprint));
    }

    public function create($collection)
    {
        return view('statamic::collections.blueprints.create', [
            'action' => cp_route('collections.blueprints.store', $collection),
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

        return redirect()
            ->cpRoute('collections.blueprints.edit', [$collection, $blueprint])
            ->with('success', __('Blueprint created'));
    }

    public function destroy($collection, $blueprint)
    {
        $blueprint = $collection->entryBlueprint($blueprint);

        $this->authorize('delete', $blueprint);

        $blueprint->delete();
    }
}
