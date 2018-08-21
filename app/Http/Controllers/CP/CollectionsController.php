<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Collection;
use Statamic\API\Helper;
use Statamic\API\Str;
use Statamic\API\User;
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

        return view('statamic::collections.show', compact('collection'));
    }

    public function manage()
    {
        return view('statamic::collections.manage', [
            'title'   => 'Collections'
        ]);
    }

    public function create()
    {
        return view('statamic::collections.create', [
            'title' => 'Creating collection'
        ]);
    }

    public function edit($collection)
    {
        $collection = Collection::whereHandle($collection);

        return view('statamic::collections.edit', [
            'title' => 'Editing collection',
            'collection' => $collection
        ]);
    }

    public function store()
    {
        $title = $this->request->input('title');

        $slug = ($this->request->has('slug')) ? $this->request->input('slug') : Str::slug($title);

        $this->validate($this->request, [
            'title' => 'required',
            'slug' => 'alpha_dash'
        ]);

        $data = compact('title');

        if ($this->request->has('order')) {
            $data['order'] = $this->request->input('order');
        }

        if ($this->request->has('fieldset')) {
            $data['fieldset'] = $this->request->input('fieldset');
        }

        $folder = Collection::create($slug);
        $folder->data($data);

        if ($this->request->has('route')) {
            $folder->route($this->request->input('route'));
        }

        $folder->save();

        return redirect()->route('collections')
            ->with('success', translate('cp.thing_created', ['thing' => $title]));
    }

    public function update($collection)
    {
        $collection = Collection::whereHandle($collection);

        $fields = $this->request->input('fields');

        $route = $fields['route'];
        unset($fields['route']);

        $data = array_merge($collection->data(), $fields);

        $collection->data($data);
        $collection->route($route);

        $collection->save();

        return redirect()->route('entries.show', $collection->path())
            ->with('success', translate('cp.thing_updated', ['thing' => $collection->title()]));
    }

    public function delete()
    {
        $ids = Helper::ensureArray($this->request->input('ids'));

        foreach ($ids as $slug) {
            Collection::whereHandle($slug)->delete();
        }

        return ['success' => true];
    }
}
