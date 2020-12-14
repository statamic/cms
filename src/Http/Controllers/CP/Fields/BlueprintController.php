<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class BlueprintController extends CpController
{
    use ManagesBlueprints;

    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function index()
    {
        $miscBlueprints = collect(Blueprint::in(''))
            ->reject(function ($blueprint) {
                return $blueprint->handle() === 'user';
            })
            ->toArray();

        return view('statamic::blueprints.index', [
            'miscBlueprints' => $miscBlueprints,
        ]);
    }

    public function create()
    {
        return view('statamic::blueprints.create', [
            'action' => cp_route('blueprints.store'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required']);

        $blueprint = $this->storeBlueprint($request, '');

        return redirect()
            ->cpRoute('blueprints.edit', [$blueprint])
            ->with('success', __('Blueprint created'));
    }

    public function edit($blueprint)
    {
        if ($blueprint === 'user') {
            return redirect(cp_route('users.blueprint.edit'));
        }

        $blueprint = Blueprint::find($blueprint);

        return view('statamic::blueprints.edit', [
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint),
        ]);
    }

    public function update(Request $request, $blueprint)
    {
        $request->validate(['sections' => 'array']);

        $this->updateBlueprint($request, Blueprint::find($blueprint));
    }

    public function destroy($blueprint)
    {
        $blueprint = Blueprint::find($blueprint);

        $blueprint->delete();

        return redirect()
            ->cpRoute('blueprints.index')
            ->with('success', __('Blueprint deleted'));
    }
}
