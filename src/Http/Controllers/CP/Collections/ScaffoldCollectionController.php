<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Facades;
use Statamic\Facades\File;
use Statamic\Fields\Blueprint;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Str;

class ScaffoldCollectionController extends CpController
{
    public function index($collection)
    {
        $this->authorize('view', $collection, __('You are not authorized to view this collection.'));

        return view('statamic::collections.scaffold', compact('collection'));
    }

    public function create(Request $request, $collection)
    {
        $this->authorize('store', CollectionContract::class, __('You are not authorized to scaffold resources.'));

        // Make the blueprint
        if ($blueprint = $this->request->get('blueprint')) {
            $this->makeBlueprint($blueprint, $collection);
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
            'redirect' => route('statamic.cp.collections.show', $request->collection->handle()),
        ];
    }

    private function makeBlueprint($title, $collection)
    {
        $handle = Str::snake($title);

        // Don't overwrite existing
        if (Facades\Blueprint::find($handle)) {
            return;
        }

        $blueprint = (new Blueprint)
            ->setHandle($handle)
            ->setNamespace('collections.'.$collection->handle())
            ->setContents([
                'title' => $title,
                'sections' => [
                    'main' => [
                        'display' => __('Main'),
                        'fields' => [],
                    ],
                ],
            ])->save();
    }

    private function makeTemplate($filename)
    {
        $file = resource_path("views/{$filename}.antlers.html");

        // Don't overwrite existing
        if (! File::get($file)) {
            File::put($file, '');
        }

        // Set the template
        $this->request->collection->template($filename)->save();
    }
}
