<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Fields\ManagesBlueprints;

class AssetContainerBlueprintController extends CpController
{
    use ManagesBlueprints;

    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function edit($container)
    {
        $blueprint = $container->blueprint();

        return view('statamic::assets.containers.blueprints.edit', [
            'container' => $container,
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint),
        ]);
    }

    public function update(Request $request, $container)
    {
        $request->validate(['sections' => 'array']);

        $this->updateBlueprint($request, $container->blueprint());
    }
}
