<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Illuminate\Http\Request;
use Statamic\CP\Breadcrumbs\Breadcrumb;
use Statamic\CP\Breadcrumbs\Breadcrumbs;
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

        Breadcrumbs::push(new Breadcrumb(
            text: $blueprint->renderableNamespace(),
        ));

        Breadcrumbs::push(new Breadcrumb(
            text: $blueprint->title(),
            icon: 'blueprints',
            url: $blueprint->editAdditionalBlueprintUrl(),
            links: Blueprint::in($blueprint->namespace())
                ->reject(fn ($b) => $b->handle() === $blueprint->handle())
                ->map(function ($b) {
                    return [
                        'text' => $b->title(),
                        'icon' => 'blueprints',
                        'url' => $b->editAdditionalBlueprintUrl(),
                    ];
                })
                ->all(),
        ));

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
