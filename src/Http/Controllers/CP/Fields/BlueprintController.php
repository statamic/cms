<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Str;

class BlueprintController extends CpController
{
    use ManagesBlueprints;

    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function index()
    {
        $additional = Blueprint::getAdditionalNamespaces()
            ->map(function ($path, $key) {
                return [
                    'title' => str_replace('.', ' ', Str::humanize($key)),
                    'blueprints' => Blueprint::in($key)
                        ->map(function ($blueprint) {
                            return [
                                'handle' => $blueprint->handle(),
                                'namespace' => $blueprint->namespace(),
                                'title' => $blueprint->title(),
                                'reset_url' => $blueprint->resetAdditionalBlueprintUrl(),
                                'is_resettable' => $blueprint->isResettable(),
                            ];
                        })
                        ->sortBy('title')
                        ->values(),
                ];
            })
            ->reject(fn ($additional) => $additional['blueprints']->isEmpty())
            ->sortBy('title');

        return view('statamic::blueprints.index', [
            'additional' => $additional,
        ]);
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
