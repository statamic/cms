<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Str;
use Statamic\API\User;
use Statamic\API\Helper;
use Illuminate\Http\Request;
use Statamic\API\Collection;
use Statamic\Contracts\Data\Entries\Collection as CollectionContract;

class CollectionsController extends CpController
{
    public function index()
    {
        $this->authorize('index', CollectionContract::class, 'You are not authorized to view any collections.');

        $collections = Collection::all()->filter(function ($collection) {
            return request()->user()->can('view', $collection);
        });

        return view('statamic::collections.index', compact('collections'));
    }

    public function show($collection)
    {
        $collection = Collection::whereHandle($collection);
        $entries = $collection->entries()->toJson();

        return view('statamic::collections.show', compact('collection', 'entries'));
    }

    public function create()
    {
        $this->authorize('create', CollectionContract::class, 'You are not authorized to create collections.');

        return view('statamic::collections.create');
    }

    public function edit($collection)
    {
        $collection = Collection::whereHandle($collection);

        $this->authorize('edit', $collection, 'You are not authorized to edit collections.');

        return view('statamic::collections.edit', compact('collection'));
    }

    public function store(Request $request)
    {
        $this->authorize('store', CollectionContract::class, 'You are not authorized to create collections.');

        $data = $request->validate([
            'title' => 'required',
            'handle' => 'nullable|alpha_dash',
            'template' => 'nullable',
            'fieldset' => 'nullable',
            'route' => 'nullable',
            'order' => 'nullable',
        ]);

        $handle = $request->handle ?? snake_case($request->title);

        $collection = tap(Collection::create($handle))
            ->data(array_except($data, 'handle'))
            ->save();

        return redirect()
            ->route('statamic.cp.collections.edit', $collection->path())
            ->with('success', 'Collection created.');
    }

    public function update(Request $request, $collection)
    {
        $collection = Collection::whereHandle($collection);

        $this->authorize('update', $collection, 'You are not authorized to edit collections.');

        $data = $request->validate([
            'title' => 'required',
            'template' => 'nullable',
            'fieldset' => 'nullable',
            'route' => 'nullable',
        ]);

        tap($collection)
            ->data(array_merge($collection->data(), $data))
            ->save();

        return redirect()
            ->route('statamic.cp.collections.edit', $collection->path())
            ->with('success', 'Collection updated');
    }

    public function destroy($collection)
    {
        $collection = Collection::whereHandle($collection);

        $this->authorize('delete', $collection, 'You are not authorized to delete collections.');

        $collection->delete();

        return redirect()
            ->route('statamic.cp.collections.index')
            ->with('success', 'Collection deleted.');
    }
}
