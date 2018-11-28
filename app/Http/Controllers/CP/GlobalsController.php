<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Helper;
use Statamic\API\GlobalSet;
use Illuminate\Http\Request;
use Statamic\Fields\Validation;
use Statamic\Contracts\Data\Globals\GlobalSet as GlobalSetContract;

class GlobalsController extends CpController
{
    public function index()
    {
        // TODO: Authorization

        return view('statamic::globals.index', [
            'globals' => GlobalSet::all()->toArray()
        ]);
    }

    public function edit($set)
    {
        if (! $set = GlobalSet::find($set)) {
            return $this->pageNotFound();
        }

        // TODO: Authorization

        $blueprint = $set->blueprint();

        // event(new PublishBlueprintFound($blueprint, 'globals', $set)); // TODO

        $fields = $blueprint
            ->fields()
            ->addValues($set->data())
            ->preProcess();

        $values = $fields->values();

        return view('statamic::globals.edit', [
            'set' => $set,
            'blueprint' => $blueprint,
            'values' => $values,
        ]);
    }

    public function update(Request $request, $set)
    {
        if (! $set = GlobalSet::find($set)) {
            return $this->pageNotFound();
        }

        // TODO: Authorization

        $fields = $set->blueprint()->fields()->addValues($request->all())->process();

        $validation = (new Validation)->fields($fields);

        $request->validate($validation->rules());

        foreach ($fields->values() as $key => $value) {
            $set->set($key, $value);
        }

        $set->save();

        // TODD: Localization

        return response('', 204);
    }

    public function updateMeta(Request $request, $set)
    {
        if (! $set = GlobalSet::find($set)) {
            return $this->pageNotFound();
        }

        $request->validate([
            'title' => 'required',
            'handle' => 'nullable|alpha_dash',
            'blueprint' => 'nullable'
        ]);

        $set
            ->set('title', $request->title)
            ->set('blueprint', $request->blueprint)
            ->handle($request->handle ?? snake_case($request->title))
            ->save();

        return response('', 204);
    }

    public function create()
    {
        return view('statamic::globals.create');
    }

    public function store(Request $request)
    {
        $this->authorize('store', GlobalSetContract::class, 'You are not authorized to create global sets.');

        $data = $request->validate([
            'title' => 'required',
            'handle' => 'nullable|alpha_dash',
            'blueprint' => 'nullable'
        ]);

        $handle = $request->handle ?? snake_case($request->title);

        $global = GlobalSet::create($handle)
            ->with(array_except($data, 'handle'))
            ->ensureId() // TODO: Shouldn't need to do this.
            ->save();

        return redirect($global->editUrl())
            ->with('success', __('Global Set created'));
    }

    public function destroy($set)
    {
        // TODO: Authorization

        if (! $set = GlobalSet::find($set)) {
            return $this->pageNotFound();
        }

        $set->delete();

        return response('', 204);
    }
}
