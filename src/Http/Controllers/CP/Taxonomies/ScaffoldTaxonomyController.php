<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Contracts\Taxonomies\Taxonomy as TaxonomyContract;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\View\Scaffolding\TemplateGenerator;

use function Statamic\trans as __;

class ScaffoldTaxonomyController extends CpController
{
    public function index($taxonomy)
    {
        $this->authorize('store', TaxonomyContract::class, __('You are not authorized to scaffold resources.'));

        return Inertia::render('taxonomies/Scaffold', [
            'taxonomy' => $taxonomy,
            'route' => cp_route('taxonomies.scaffold.create', $taxonomy),
        ]);
    }

    public function create(Request $request, TemplateGenerator $generator, $taxonomy)
    {
        $this->authorize('store', TaxonomyContract::class, __('You are not authorized to scaffold resources.'));

        if ($indexPath = $this->request->get('index')) {
            $generator
                ->scaffold('taxonomy.index', [
                    'taxonomy' => $taxonomy,
                ])
                ->save($indexPath);

            $taxonomy->template($indexPath)->save();
        }

        if ($showPath = $this->request->get('show')) {
            $generator
                ->scaffold('taxonomy.show', [
                    'taxonomy' => $taxonomy,
                ])
                ->save($showPath);

            $taxonomy->termTemplate($showPath)->save();
        }

        session()->flash('success', __('Views created successfully'));

        return redirect()->route('statamic.cp.taxonomies.show', $request->taxonomy->handle());
    }
}
