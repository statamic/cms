<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class AdditionalBlueprintController extends CpController
{
    use ManagesBlueprints;

    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function edit($namespace, $handle)
    {
        $blueprint = Blueprint::find($namespace.'::'.$handle);

        if (! $blueprint) {
            throw new NotFoundHttpException;
        }

        return view('statamic::blueprints.edit', [
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint),
        ]);
    }

    public function update(Request $request, $namespace, $handle)
    {
        $blueprint = Blueprint::find($namespace.'::'.$handle);

        if (! $blueprint) {
            throw new NotFoundHttpException;
        }

        $request->validate([
            'title' => 'required',
            'tabs' => 'array',
        ]);

        $this->updateBlueprint($request, $blueprint);
    }

    public function reset($namespace, $handle)
    {
        $blueprint = Blueprint::find($namespace.'::'.$handle);

        if (! $blueprint) {
            throw new NotFoundHttpException;
        }

        $blueprint->reset();

        return response('');
    }
}
