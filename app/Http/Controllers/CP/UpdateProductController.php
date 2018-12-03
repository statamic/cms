<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\Statamic;
use Statamic\API\Addon;
use Illuminate\Http\Request;
use Statamic\Updater\Updater;
use Statamic\Updater\Changelog;
use Facades\Statamic\Updater\UpdatesCount;
use Facades\Statamic\Console\Processes\Composer;

class UpdateProductController extends CpController
{
    /**
     * Product updates overview.
     *
     * @param string $product
     */
    public function index($product)
    {
        $this->authorize('view updates');

        $package = $this->getPackage($product);

        return view('statamic::updater.index', [
            'title' => 'Updates',
            'slug' => $product,
            'package' => $package,
        ]);
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
     * Get updatable package from product slug.
     *
     * @param string $product
     * @return string
     */
    private function getPackage(string $product)
    {
        if ($product === Statamic::CORE_SLUG) {
            return Statamic::CORE_REPO;
        }

        $package = Addon::all()->first(function ($addon) use ($product) {
            return $addon->marketplaceSlug() === $product;
        });

        abort_unless($package, 404);

        return $package->package();
    }
}
