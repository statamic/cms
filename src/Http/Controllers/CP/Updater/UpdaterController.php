<?php

namespace Statamic\Http\Controllers\CP\Updater;

use Facades\Statamic\Marketplace\Marketplace;
use Facades\Statamic\Updater\UpdatesOverview;
use Illuminate\Http\Request;
use Inertia\Inertia;
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

        $changelog = Marketplace::statamic()->changelog();

        return Inertia::render('updater/Index', [
            'requestError' => $licenses->requestFailed(),
            'statamic' => [
                'currentVersion' => $changelog->currentVersion(),
                'availableUpdatesCount' => $changelog->availableUpdatesCount(),
            ],
            'addons' => $addons->filter->existsOnMarketplace()->map(fn ($addon) => [
                'name' => $addon->name(),
                'slug' => $addon->slug(),
                'version' => $addon->version(),
                'availableUpdatesCount' => $addon->changelog()->availableUpdatesCount(),
            ])->values()->all(),
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
