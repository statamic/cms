<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Collection;
use Statamic\API\Helper;
use Statamic\API\Str;
use Statamic\API\User;

class CollectionsController extends CpController
{
    /**
     * The main collections route, which either browses the first
     * collection or redirects to the collection listing.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $this->access('collections:*:edit');

        $collections = collect(Collection::all())->filter(function ($collection) {
            return User::getCurrent()->can("collections:{$collection->path()}:edit");
        })->all();

        if (count($collections) === 1) {
            return redirect()->route('entries.show', reset($collections)->path());
        }

        return view('collections.index', [
            'title'   => 'Collections'
        ]);
    }

    public function manage()
    {
        return view('collections.manage', [
            'title'   => 'Collections'
        ]);
    }

    public function get()
    {
        $collections = [];

        foreach (Collection::all() as $collection) {
            if (! User::getCurrent()->can("collections:{$collection->path()}:edit")) {
                continue;
            }

            $collections[] = [
                'id'          => $collection->path(),
                'title'       => $collection->title(),
                'entries'     => $collection->count(),
                'edit_url'    => $collection->editUrl(),
                'create_url'  => route('entry.create', $collection->path()),
                'entries_url' => route('entries.show', $collection->path())
            ];
        }

        return ['columns' => ['title'], 'items' => $collections];
    }

    public function create()
    {
        return view('collections.create', [
            'title' => 'Creating collection'
        ]);
    }

    public function edit($collection)
    {
        $collection = Collection::whereHandle($collection);

        return view('collections.edit', [
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
