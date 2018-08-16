<?php

namespace Statamic\Http\Controllers\CP;

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
        // $this->access('taxonomies:*:view'); // TODO

        $taxonomies = collect(Taxonomy::all());
        // TODO: Reinstate filtering out taxonomies the user is not allowed to access
        // ->filter(function ($taxonomy) {
        //     return User::getCurrent()->can("taxonomies:{$taxonomy->path()}:view");
        // })->all();

        // TODO: Reinstate the redirect
        // if (count($taxonomies) === 1) {
        //     return redirect()->route('terms.show', reset($taxonomies)->path());
        // }

        return view('statamic::taxonomies.index', [
            'title'   => 'Taxonomies',
            'taxonomies' => $taxonomies
        ]);
    }

    public function manage()
    {
        return view('statamic::taxonomies.manage', [
            'title'   => 'Taxonomies'
        ]);
    }

    public function create()
    {
        return view('statamic::taxonomies.create', [
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

        return view('statamic::taxonomies.edit', [
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
