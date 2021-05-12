<?php

namespace Statamic\Http\Controllers\CP\Globals;

use Illuminate\Http\Request;
use Statamic\Facades\GlobalSet;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Fields\ManagesBlueprints;

class GlobalsBlueprintController extends CpController
{
    use ManagesBlueprints;

    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function edit($set)
    {
        if (! $set = GlobalSet::find($set)) {
            return $this->pageNotFound();
        }

        $blueprint = $this->blueprint($set);

        return view('statamic::globals.blueprints.edit', [
            'set' => $set,
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint),
        ]);
    }

    public function update(Request $request, $set)
    {
        if (! $set = GlobalSet::find($set)) {
            return $this->pageNotFound();
        }

        $request->validate(['sections' => 'array']);

        $this->updateBlueprint($request, $this->blueprint($set));
    }

    private function blueprint($set)
    {
        return tap($set->blueprint() ?? $set->inDefaultSite()->blueprint())
            ->setHandle($set->handle())
            ->setNamespace('globals');
    }
}
