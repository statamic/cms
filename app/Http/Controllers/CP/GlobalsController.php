<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Helper;
use Statamic\API\GlobalSet;
use Illuminate\Http\Request;
use Statamic\Fields\Validation;
use Statamic\Events\Data\PublishBlueprintFound;
use Statamic\Contracts\Data\Globals\GlobalSet as GlobalSetContract;

class GlobalsController extends CpController
{
    public function index()
    {
        $globals = GlobalSet::all()->filter(function ($set) {
            return user()->can('view', $set);
        })->tap(function ($globals) {
            $this->authorizeIf($globals->isEmpty(), 'create', GlobalSetContract::class);
        })->map(function ($set) {
            return [
                'id' => $set->id(),
                'handle' => $set->handle(),
                'title' => $set->title(),
                'deleteable' => user()->can('delete', $set),
                'edit_url' => $set->editUrl(),
            ];
        })->values();

        return view('statamic::globals.index', [
            'globals' => $globals
        ]);
    }

    public function edit($id, $handle, $site)
    {
        if (! $set = GlobalSet::find($id)) {
            return $this->pageNotFound();
        }

        if (! $set->sites()->contains($site)) {
            return $this->pageNotFound();
        }

        $set = $set->inOrClone($site);

        $this->authorize('edit', $set);

        $blueprint = $set->blueprint();

        event(new PublishBlueprintFound($blueprint, 'globals', $set));

        $fields = $blueprint
            ->fields()
            ->addValues($set->data())
            ->preProcess();

        $values = $fields->values();

        return view('statamic::globals.edit', [
            'set' => $set,
            'blueprint' => $blueprint,
            'values' => $values,
            'meta' => $fields->meta(),
        ]);
    }

    public function update(Request $request, $id, $handle, $site)
    {
        if (! $set = GlobalSet::find($id)) {
            return $this->pageNotFound();
        }

        $set = $set->inOrClone($site);

        $this->authorize('edit', $set);

        $fields = $set->blueprint()->fields()->addValues($request->all())->process();

        $validation = (new Validation)->fields($fields);

        $request->validate($validation->rules());

        foreach ($fields->values() as $key => $value) {
            $set->set($key, $value);
        }

        $set->save();

        return $set->toArray();
    }

    public function updateMeta(Request $request, $set)
    {
        if (! $set = GlobalSet::find($set)) {
            return $this->pageNotFound();
        }

        $this->authorize('create', $set);

        $request->validate([
            'title' => 'required',
            'handle' => 'nullable|alpha_dash',
            'blueprint' => 'nullable'
        ]);

        $set
            ->title($request->title)
            ->blueprint($request->blueprint)
            ->handle($request->handle ?? snake_case($request->title))
            ->save();

        return response('', 204);
    }

    public function create()
    {
        $this->authorize('create', GlobalSetContract::class);

        return view('statamic::globals.create');
    }

    public function store(Request $request)
    {
        $this->authorize('store', GlobalSetContract::class);

        $data = $request->validate([
            'title' => 'required',
            'handle' => 'nullable|alpha_dash',
            'blueprint' => 'string|nullable'
        ]);

        $handle = $request->handle ?? snake_case($request->title);

        $global = GlobalSet::create($handle)
            ->with(array_except($data, 'handle'))
            ->ensureId() // TODO: Shouldn't need to do this.
            ->save();

        session()->flash('message', __('Global Set created'));

        return [
            'redirect' => $global->editUrl()
        ];
    }

    public function destroy($set)
    {
        if (! $set = GlobalSet::find($set)) {
            return $this->pageNotFound();
        }

        $this->authorize('delete', $set);

        $set->delete();

        return response('', 204);
    }
}
