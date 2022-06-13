<?php

namespace Statamic\Http\Controllers\CP\Updater;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Marketplace\Marketplace;
use Illuminate\Http\Request;
use Statamic\Facades\Addon;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Statamic;
use Statamic\Updater\Updater;

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
            'lastInstallLog' => Composer::lastCompletedCachedOutput($product->package())['output'],
        ];
    }

    /**
     * Install explicit version.
     *
     * @param  string  $product
     * @param  Request  $request
     */
    public function install($product, Request $request)
    {
        $this->authorize('perform updates');

        $package = $product === Statamic::CORE_SLUG ? Statamic::PACKAGE : $this->getAddon($product)->package();

        return Updater::package($package)->install($request->version);
    }

    /**
     * Get updatable addon from product slug.
     *
     * @param  string  $product
     * @return \Illuminate\Support\Collection
     */
    private function getAddon($product)
    {
        return Addon::all()->first(function ($addon) use ($product) {
            return $addon->slug() === $product;
        });
    }
}
