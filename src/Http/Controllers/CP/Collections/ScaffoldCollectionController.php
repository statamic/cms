<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Facades\File;
use Statamic\Http\Controllers\CP\CpController;

class ScaffoldCollectionController extends CpController
{
    protected array $templateExtensions = [
        'antlers' => '.antlers.html',
        'blade' => '.blade.php',
    ];

    public function index($collection)
    {
        $this->authorize('store', CollectionContract::class, __('You are not authorized to scaffold resources.'));

        return view('statamic::collections.scaffold', compact('collection'));
    }

    public function create(Request $request, $collection)
    {
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

    private function getTemplateFile($filename)
    {
        $extension = Arr::get(
            $this->templateExtensions,
            config('statamic.templates.engine', 'antlers'),
            '.antlers.html'
        );

        return resource_path("views/{$filename}{$extension}");
    }

    private function makeTemplate($filename)
    {
        $file = $this->getTemplateFile($filename);

        // Don't overwrite existing
        if (! File::get($file)) {
            File::put($file, '');
        }

        // Set the template
        $this->request->collection->template($filename)->save();
    }
}
