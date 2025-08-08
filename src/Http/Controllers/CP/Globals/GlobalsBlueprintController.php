<?php

namespace Statamic\Http\Controllers\CP\Globals;

use Illuminate\Http\Request;
use Statamic\CP\Breadcrumbs\Breadcrumb;
use Statamic\CP\Breadcrumbs\Breadcrumbs;
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

        Breadcrumbs::push(new Breadcrumb(
            text: 'Globals',
        ));

        Breadcrumbs::push(new Breadcrumb(
            text: $set->title(),
            url: request()->url(),
            icon: 'globals',
            links: GlobalSet::all()
                ->reject(fn ($s) => $s->handle() === $set->handle())
                ->map(fn ($s) => [
                    'text' => $s->title(),
                    'icon' => 'globals',
                    'url' => cp_route('blueprints.globals.edit', $s->handle()),
                ])
                ->values()
                ->all(),
        ));

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

        $request->validate(['tabs' => 'array']);

        $this->updateBlueprint($request, $this->blueprint($set));
    }

    private function blueprint($set)
    {
        return tap($set->blueprint() ?? $set->inDefaultSite()->blueprint())
            ->setHandle($set->handle())
            ->setNamespace('globals');
    }
}
