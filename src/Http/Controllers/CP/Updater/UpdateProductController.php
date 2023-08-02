<?php

namespace Statamic\Http\Controllers\CP\Updater;

use Facades\Statamic\Marketplace\Marketplace;
use Statamic\Http\Controllers\CP\CpController;

class UpdateProductController extends CpController
{
    /**
     * Show product updates overview.
     *
     * @param  string  $slug
     */
    public function show($slug)
    {
        $this->authorize('view updates');

        if (! $product = Marketplace::product($slug)) {
            return $this->pageNotFound();
        }

        return view('statamic::updater.show', [
            'slug' => $slug,
            'package' => $product->package(),
            'name' => $product->name(),
        ]);
    }

    /**
     * Product changelog.
     *
     * @param  string  $slug
     */
    public function changelog($slug)
    {
        $this->authorize('view updates');

        if (! $product = Marketplace::product($slug)) {
            return $this->pageNotFound();
        }

        $changelog = $product->changelog();

        return [
            'changelog' => $changelog->get(),
            'currentVersion' => $changelog->currentVersion(),
        ];
    }
}
