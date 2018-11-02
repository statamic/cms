<?php

namespace Statamic\Http\Controllers\CP;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Updater\CoreChangelog;
use Facades\Statamic\Updater\CoreUpdater;
use Facades\Statamic\Updater\UpdatesCount;
use Illuminate\Http\Request;
use Statamic\API\Addon;
use Statamic\Statamic;

class UpdateProductController extends CpController
{
    const CORE_SLUG = 'statamic';

    public function __construct()
    {
        // Temporarily using PackToTheFuture to fake version tags until we get this hooked up to statamic/cms.
        require(base_path('vendor/statamic/cms/tests/Fakes/Composer/Package/PackToTheFuture.php'));
    }

    public function index($product)
    {
        $this->access('updater');

        $package = $this->getPackage($product);

        return view('statamic::updater.index', [
            'title' => 'Updates'
        ]);
    }

    public function changelog($product)
    {
        $this->access('updater');

        $package = $this->getPackage($product);

        return [
            'changelog' => CoreChangelog::get(),
            'currentVersion' => Statamic::version(),
            'lastInstallLog' => Composer::lastCachedOutput(Statamic::CORE_REPO),
        ];
    }

    public function update($product)
    {
        return CoreUpdater::update();
    }

    public function updateToLatest()
    {
        // Normally we can run this, but we can't require using a 2.10.* version constraint on a fake path repo.
        // return CoreUpdater::updateToLatest();

        \Tests\Fakes\Composer\Package\PackToTheFuture::setVersion('2.10.6'); // Temp!

        return CoreUpdater::installExplicitVersion('2.10.6');
    }

    public function installExplicitVersion(Request $request)
    {
        \Tests\Fakes\Composer\Package\PackToTheFuture::setVersion($request->version); // Temp!

        return CoreUpdater::installExplicitVersion($request->version);
    }

    /**
     * Get updatable package from product slug.
     *
     * @param string $product
     * @return string
     */
    private function getPackage(string $product)
    {
        if ($product === self::CORE_SLUG) {
            return Statamic::CORE_REPO;
        }

        $package = Addon::all()->first(function ($addon) use ($product) {
            return $addon->marketplaceSlug() === $product;
        });

        abort_unless($package, 404);

        return $package->package();
    }
}
