<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Structure;
use Illuminate\Http\Request;
use Statamic\Contracts\Data\Structures\Structure as StructureContract;

class StructuresController extends CpController
{
    public function index()
    {
        $this->authorize('index', StructureContract::class, 'You are not authorized to view any structures.');

        $structures = Structure::all()->filter(function ($structure) {
            return me()->can('view', $structure);
        })->map(function ($structure) {
            return [
                'id' => $structure->handle(),
                'title' => $structure->title(),
                'pages' => $structure->flattenedPages()->count(),
                'show_url' => cp_route('structures.show', $structure->handle()),
                'edit_url' => cp_route('structures.edit', $structure->handle()),
                'deletetable' => me()->can('delete', $structure)
            ];
        });

        return view('statamic::structures.index', compact('structures'));
    }

    public function edit($structure)
    {
        $structure = Structure::find($structure);

        $this->authorize('edit', $structure, 'You are not authorized to edit this structure.');

        return view('statamic::structures.edit', compact('structure'));
    }

    public function show($structure)
    {
        // TODO: Don't call a controller method, that's silly.
        $pages = app(StructurePagesController::class)->index($structure)['pages'];

        $structure = Structure::find($structure);

        $root = $structure->parent()->toArray();

        return view('statamic::structures.show', compact('structure', 'pages', 'root'));
    }

    public function update(Request $request, $structure)
    {
        //
    }

    public function create()
    {
        return view('statamic::structures.create');
    }

    public function store(Request $request)
    {
        //
    }
}
