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
        return view('statamic::blueprints.index');
    }

    public function edit(string $handle)
    {
        $blueprint = Blueprint::find($handle);

        if (! $blueprint) {
            abort(404, __('Blueprint not found'));
        }

        return view('statamic::blueprints.edit', [
            'handle' => $handle,
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint),
        ]);
    }

    public function update(string $handle, Request $request)
    {
        $request->validate(['sections' => 'array']);

        $blueprint = Blueprint::find($handle);

        if (! $blueprint) {
            abort(404, __('Blueprint not found'));
        }

        $this->updateBlueprint($request, $blueprint);
    }
}
