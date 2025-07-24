<?php

namespace Statamic\Http\Controllers\CP\Updater;

use Facades\Statamic\Marketplace\Marketplace;
use Facades\Statamic\Updater\UpdatesOverview;
use Illuminate\Http\Request;
use Statamic\Facades\Addon;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Licensing\LicenseManager as Licenses;
use Statamic\Statamic;

class UpdaterController extends CpController
{
    /**
     * Updates overview.
     */
    public function index(Licenses $licenses)
    {
        $this->authorize('view updates');

        $addons = Addon::all();

        if ($addons->isEmpty()) {
            return redirect()->route('statamic.cp.updater.product', Statamic::CORE_SLUG);
        }

        return view('statamic::updater.index', [
            'requestError' => $licenses->requestFailed(),
            'statamic' => Marketplace::statamic()->changelog(),
            'addons' => $addons->filter->existsOnMarketplace(),
        ]);
    }

    /**
     * Updates count.
     */
    public function count(Request $request)
    {
        $this->authorize('view updates');

        return UpdatesOverview::count();
    }
}
