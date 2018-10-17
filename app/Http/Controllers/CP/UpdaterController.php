<?php

namespace Statamic\Http\Controllers\CP;

use Facades\Statamic\Console\Processes\Composer;
use Facades\Statamic\Updater\CoreChangelog;
use Facades\Statamic\Updater\CoreUpdater;
use Facades\Statamic\Updater\UpdatesCount;
use Illuminate\Http\Request;
use Statamic\Statamic;

class UpdaterController extends CpController
{
    public function __construct()
    {
        // Temporarily using PackToTheFuture to fake version tags until we get this hooked up to statamic/cms.
        require(base_path('vendor/statamic/cms/tests/Fakes/Composer/Package/PackToTheFuture.php'));
    }

    public function index()
    {
        $this->access('updater');

        return redirect()->route('statamic.cp.updater.product.index', ['statamic']);
    }

    public function count(Request $request)
    {
        $this->access('updater');

        return UpdatesCount::get($request->input('clearCache', false));
    }

    public function product()
    {
        $this->access('updater');

        return view('statamic::updater.index', [
            'title' => 'Updates'
        ]);
    }

    public function changelog()
    {
        $this->access('updater');

        return [
            'changelog' => CoreChangelog::get(),
            'currentVersion' => Statamic::version(),
            'lastInstallLog' => Composer::lastCachedOutput(Statamic::CORE_REPO),
        ];
    }

    public function update()
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
}
