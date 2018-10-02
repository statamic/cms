<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API;
use Illuminate\Http\Request;
use Statamic\Fields\Blueprint;

class BlueprintController extends CpController
{
    public function index()
    {
        $this->authorize('index', Blueprint::class, 'You are not authorized to access fieldsets.');

        $blueprints = API\Blueprint::all()->map(function ($blueprint) {
            return [
                'id' => $blueprint->handle(),
                'handle' => $blueprint->handle(),
                'title' => $blueprint->title(),
                'sections' => $blueprint->sections()->count(),
                'fields' => $blueprint->fields()->all()->count(),
                'edit_url' => $blueprint->editUrl(),
            ];
        })->values();

        return view('statamic::blueprints.index', compact('blueprints'));
    }
}
