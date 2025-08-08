<?php

namespace Statamic\Http\Controllers\CP\Navigation;

use Illuminate\Http\Request;
use Statamic\CP\Breadcrumbs\Breadcrumb;
use Statamic\CP\Breadcrumbs\Breadcrumbs;
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

        Breadcrumbs::push(new Breadcrumb(
            text: 'Navigaton',
        ));

        Breadcrumbs::push(new Breadcrumb(
            text: $nav->title(),
            url: request()->url(),
            icon: 'navigation',
            links: Nav::all()
                ->reject(fn ($n) => $n->handle() === $nav->handle())
                ->map(fn ($n) => [
                    'text' => $n->title(),
                    'icon' => 'navigation',
                    'url' => cp_route('blueprints.navigation.edit', $n->handle()),
                ])
                ->values()
                ->all(),
        ));

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

        $request->validate(['tabs' => 'array']);

        $this->updateBlueprint($request, $nav->blueprint());
    }
}
