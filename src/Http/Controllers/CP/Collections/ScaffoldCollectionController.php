<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\Facades;
use Statamic\Support\Str;
use Statamic\Facades\File;
use Illuminate\Http\Request;
use Statamic\Fields\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Contracts\Entries\Collection as CollectionContract;

class ScaffoldCollectionController extends CpController
{
    public function index($collection)
    {
        $this->authorize('view', $collection, 'You are not authorized to view this collection.');

        return view('statamic::collections.scaffold', compact('collection'));
    }

    public function create(Request $request)
    {
        $this->authorize('store', CollectionContract::class, 'You are not authorized to scaffold resources.');

        // Make the blueprint
        if ($blueprint = $this->request->get('blueprint')) {
            $this->makeBlueprint($blueprint);
        }

        // Make the index template
        if ($index = $this->request->get('index')) {
            $this->makeTemplate($index);
        }

        // Make the show template
        if ($show = $this->request->get('show')) {
            $this->makeTemplate($show);
        }

        session()->flash('success', __('Resources scaffolded'));

        return [
            'redirect' => route('statamic.cp.collections.show', $request->collection->handle())
        ];
    }

    private function makeBlueprint($title)
    {
        $handle = Str::snake($title);

        if (Facades\Blueprint::find($handle)) {
            return;
        }

        $blueprint = (new Blueprint)
            ->setHandle($handle)
            ->setContents([
                'title' => $title,
                'sections' => [
                    'main' => [
                        'display' => 'Main',
                        'fields' => []
                    ]
                ]
            ])->save();
    }

    private function makeTemplate($filename)
    {
        $file = resource_path("views/{$filename}.antlers.html");

        // Don't overwrite existing
        if (! File::get($file)) {
            File::put($file, '');
        }
    }
}
