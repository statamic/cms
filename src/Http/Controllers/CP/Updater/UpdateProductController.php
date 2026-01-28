<?php

namespace Statamic\Http\Controllers\CP\Updater;

use Facades\Statamic\Marketplace\Marketplace;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Http\Controllers\CP\CpController;

class UpdateProductController extends CpController
{
    /**
     * Show product updates overview.
     *
     * @param  string  $slug
     */
    public function show($marketplaceProductSlug)
    {
        $this->authorize('view updates');

        if (! $product = Marketplace::product($marketplaceProductSlug)) {
            return $this->pageNotFound();
        }

        return Inertia::render('updater/Show', [
            'slug' => $marketplaceProductSlug,
            'package' => $product->package(),
            'name' => $product->name(),
        ]);
    }

    /**
     * Product changelog.
     *
     * @param  string  $slug
     */
    public function changelog(Request $request, $marketplaceProductSlug)
    {
        $this->authorize('view updates');

        if (! $product = Marketplace::product($marketplaceProductSlug)) {
            return $this->pageNotFound();
        }

        $changelog = $product->changelog();

        $paginated = $changelog->paginate(
            $request->input('page', 1),
            $request->input('perPage', 10)
        );

        return [
            'changelog' => $paginated['data'],
            'currentVersion' => $changelog->currentVersion(),
            'meta' => $paginated['meta'],
        ];
    }
}
