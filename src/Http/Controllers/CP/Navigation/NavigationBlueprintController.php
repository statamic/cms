<?php

namespace Statamic\Http\Controllers\CP\Navigation;

use Illuminate\Http\Request;
use Statamic\Facades\Nav;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Fields\ManagesBlueprints;

class NavigationBlueprintController extends CpController
{
    use ManagesBlueprints;

    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function edit($nav)
    {
        if (! $nav = Nav::find($nav)) {
            return $this->pageNotFound();
        }

        $blueprint = $nav->blueprint();

        return view('statamic::navigation.blueprints.edit', [
            'nav' => $nav,
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint),
        ]);
    }

    public function update(Request $request, $nav)
    {
        if (! $nav = Nav::find($nav)) {
            return $this->pageNotFound();
        }

        $request->validate(['sections' => 'array']);

        $this->updateBlueprint($request, $nav->blueprint());
    }
}
