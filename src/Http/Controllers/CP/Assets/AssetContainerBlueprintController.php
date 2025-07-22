<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Statamic\CP\Breadcrumbs\Breadcrumb;
use Statamic\CP\Breadcrumbs\Breadcrumbs;
use Statamic\Facades\AssetContainer;
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

        Breadcrumbs::push(new Breadcrumb(
            text: 'Asset Containers',
        ));

        Breadcrumbs::push(new Breadcrumb(
            text: $container->title(),
            url: request()->url(),
            icon: 'assets',
            links: AssetContainer::all()
                ->reject(fn ($c) => $c->handle() === $container->handle())
                ->map(fn ($c) => [
                    'text' => $c->title(),
                    'icon' => 'assets',
                    'url' => cp_route('blueprints.asset-containers.edit', $c->handle()),
                ])
                ->values()
                ->all(),
        ));

        return view('statamic::assets.containers.blueprints.edit', [
            'container' => $container,
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint),
        ]);
    }

    public function update(Request $request, $container)
    {
        $request->validate(['tabs' => 'array']);

        $this->updateBlueprint($request, $container->blueprint());
    }
}
