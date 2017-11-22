<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Content;
use Statamic\API\Helper;
use Statamic\API\Str;
use Statamic\API\User;
use Statamic\API\Taxonomy;
use Statamic\CP\FieldsetFactory;

class TaxonomiesController extends CpController
{
    /**
     * The main taxonomies route, which either browses the first
     * group or redirects to the group listing.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $this->access('taxonomies:*:edit');

        $groups = collect(Taxonomy::all())->filter(function ($taxonomy) {
            return User::getCurrent()->can("taxonomies:{$taxonomy->path()}:edit");
        })->all();

        if (count($groups) === 1) {
            return redirect()->route('terms.show', reset($groups)->path());
        }

        return view('taxonomies.index', [
            'title'   => 'Taxonomies'
        ]);
    }

    public function manage()
    {
        return view('taxonomies.manage', [
            'title'   => 'Taxonomies'
        ]);
    }

    public function get()
    {
        $groups = [];

        foreach (Taxonomy::all() as $group) {
            if (! User::getCurrent()->can("taxonomies:{$group->path()}:edit")) {
                continue;
            }

            $groups[] = [
                'id'             => $group->path(),
                'title'          => $group->title(),
                'taxonomies'     => $group->count(),
                'edit_url'       => $group->editUrl(),
                'create_url'     => route('term.create', $group->path()),
                'terms_url'      => route('terms.show', $group->path())
            ];
        }

        return ['columns' => ['title'], 'items' => $groups];
    }

    public function create()
    {
        return view('taxonomies.create', [
            'title' => trans('cp.create_taxonomy'),
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

        if ($this->request->has('fieldset')) {
            $data['fieldset'] = $this->request->input('fieldset');
        }

        $folder = Taxonomy::create($slug);
        $folder->data($data);

        if ($this->request->has('route')) {
            $folder->route($this->request->input('route'));
        }

        $folder->save();

        return redirect()->route('terms.show', $slug)->with('success', 'Taxonomy group created.');
    }

    public function edit($group)
    {
        $group = Taxonomy::whereHandle($group);

        return view('taxonomies.edit', [
            'title' => 'Updating ' . $group->title(),
            'group' => $group
        ]);
    }

    public function update($group)
    {
        $group = Taxonomy::whereHandle($group);

        $group->data([
            'title' => $this->request->input('title'),
            'fieldset' => $this->request->input('fieldset')
        ]);

        $group->route($this->request->input('route'));

        $group->save();

        return redirect()->back()->with('success', 'Taxonomy group updated.');
    }

    public function delete()
    {
        $ids = Helper::ensureArray($this->request->input('ids'));

        foreach ($ids as $group_name) {
            Taxonomy::whereHandle($group_name)->delete();
        }

        return ['success' => true];
    }
}
