<?php

namespace Statamic\Http\Controllers\CP\Updater;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Http\Request;
use Statamic\Facades\Addon;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Statamic;
use Statamic\Updater\Changelog;
use Statamic\Updater\Updater;

class UpdateProductController extends CpController
{
    /**
     * Show product updates overview.
     *
     * @param string $product
     */
    public function show($product)
    {
        $this->authorize('view updates');

        if ($addon = $this->getAddon($product)) {
            $data['package'] = $addon->package();
            $data['name'] = $addon->name();
        } elseif ($product === Statamic::CORE_SLUG) {
            $data['package'] = Statamic::CORE_REPO;
            $data['name'] = 'Statamic';
        } else {
            abort(404);
        }

        return view('statamic::updater.show', array_merge($data, [
            'slug' => $product,
        ]));
    }

    /**
     * Product changelog.
     *
     * @param string $product
     */
    public function changelog($product)
    {
        $this->authorize('view updates');

        $changelog = Changelog::product($product);

        return [
            'changelog' => $changelog->get(),
            'currentVersion' => $changelog->currentVersion(),
            'lastInstallLog' => Composer::lastCompletedCachedOutput($changelog->composerPackage())['output'],
        ];
    }

    /**
     * Update using version constraint.
     *
     * @param string $product
     */
    public function update($product)
    {
        $this->authorize('perform updates');

        return Updater::product($product)->update();
    }

    /**
     * Update to latest version.
     *
     * @param string $product
     */
    public function updateToLatest($product)
    {
        $this->authorize('perform updates');

        return Updater::product($product)->updateToLatest();
    }

    /**
     * Install explicit version.
     *
     * @param string $product
     * @param Request $request
     */
    public function installExplicitVersion($product, Request $request)
    {
        $this->authorize('perform updates');

        return Updater::product($product)->installExplicitVersion($request->version);
    }

    /**
     * Get updatable addon from product slug.
     *
     * @param string $product
     * @return \Illuminate\Support\Collection
     */
    private function getAddon($product)
    {
        return Addon::all()->first(function ($addon) use ($product) {
            return $addon->marketplaceSlug() === $product;
        });
    }
}
