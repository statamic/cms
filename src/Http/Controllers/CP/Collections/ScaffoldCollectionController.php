<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Collection;
use Statamic\Facades\File;
use Statamic\Http\Controllers\CP\CpController;

class ScaffoldCollectionController extends CpController
{
    public function index($collection)
    {
        $collection = Collection::findByHandle($collection);
        if (! $collection) {
            $handle = func_get_arg(0);
            new NotFoundHttpException("Collection [$handle] not found.");
        }

        $this->authorize('view', $collection, __('You are not authorized to view this collection.'));

        return view('statamic::collections.scaffold', compact('collection'));
    }

    public function create(Request $request, $collection)
    {
        $collection = Collection::findByHandle($collection);
        if (! $collection) {
            $handle = func_get_arg(1);
            new NotFoundHttpException("Collection [$handle] not found.");
        }

        $this->authorize('store', CollectionContract::class, __('You are not authorized to scaffold resources.'));

        // Make the index template
        if ($index = $this->request->get('index')) {
            $this->makeTemplate($index);
        }

        // Make the show template
        if ($show = $this->request->get('show')) {
            $this->makeTemplate($show);
        }

        session()->flash('success', __('Views created successfully'));

        return [
            'redirect' => route('statamic.cp.collections.show', $request->collection->handle()),
        ];
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
