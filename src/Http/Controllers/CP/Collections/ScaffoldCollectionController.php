<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Contracts\Entries\Collection as CollectionContract;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\View\Scaffolding\TemplateGenerator;

class ScaffoldCollectionController extends CpController
{
    public function index($collection)
    {
        $this->authorize('store', CollectionContract::class, __('You are not authorized to scaffold resources.'));

        return Inertia::render('collections/Scaffold', [
            'collection' => $collection,
            'route' => cp_route('collections.scaffold.create', $collection),
        ]);
    }

    public function create(Request $request, $collection)
    {
        $this->authorize('store', CollectionContract::class, __('You are not authorized to scaffold resources.'));

        $generator = TemplateGenerator::make();

        // Make the index template
        if ($indexPath = $this->request->get('index')) {
            $generator
                ->scaffold('collection.index', [
                    'collection' => $collection,
                ])
                ->save($indexPath);

            $collection->template($indexPath)->save();
        }

        // Make the show template
        if ($showPath = $this->request->get('show')) {
            $generator
                ->scaffold('collection.show', [
                    'collection' => $collection,
                ])
                ->save($showPath);

            $collection->template($showPath)->save();
        }

        session()->flash('success', __('Views created successfully'));

        return redirect()->route('statamic.cp.collections.show', $request->collection->handle());
    }
}
