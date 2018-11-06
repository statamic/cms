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
    public function __construct()
    {
        // Temporarily using PackToTheFuture to fake version tags until we get this hooked up to statamic/cms.
        require(base_path('vendor/statamic/cms/tests/Fakes/Composer/Package/PackToTheFuture.php'));
    }

    /**
     * Product updates overview.
     *
     * @param string $product
     */
    public function index($product)
    {
        $this->access('updater');

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
        $this->access('updater');

        $changelog = Changelog::product($product);

        return [
            'changelog' => $changelog->get(),
            'currentVersion' => $changelog->currentVersion(),
            'lastInstallLog' => Composer::lastCachedOutput(Statamic::CORE_REPO),
        ];
    }

    /**
     * Update using version constraint.
     *
     * @param string $product
     */
    public function update($product)
    {
        return Updater::product($product)->update();
    }

    /**
     * Update to latest version.
     *
     * @param string $product
     */
    public function updateToLatest($product)
    {
        // Temp!
        if ($product == Statamic::CORE_SLUG) {
            \Tests\Fakes\Composer\Package\PackToTheFuture::setVersion('2.10.7');
            return Updater::product($product)->installExplicitVersion('2.10.7');
        }

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
        // Temp!
        if ($product == Statamic::CORE_SLUG) {
            \Tests\Fakes\Composer\Package\PackToTheFuture::setVersion($request->version);
        }

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
