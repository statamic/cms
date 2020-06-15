<?php

namespace Statamic\Http\Controllers\CP\Updater;

use Facades\Statamic\Updater\UpdatesOverview;
use Illuminate\Http\Request;
use Statamic\Facades\Addon;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Statamic;
use Statamic\Updater\Changelog;

class UpdaterController extends CpController
{
    /**
     * Updates overview.
     */
    public function index()
    {
        $this->authorize('view updates');

        $addons = $this->getUpdatableAddons();

        if ($addons->isEmpty()) {
            return redirect()->route('statamic.cp.updater.product', Statamic::CORE_SLUG);
        }

        $statamicChangelog = Changelog::product(Statamic::CORE_SLUG);

        return view('statamic::updater.index', compact('statamicChangelog', 'addons'));
    }

    /**
     * Updates count.
     *
     * @param Request $request
     */
    public function count(Request $request)
    {
        $this->authorize('view updates');

        return UpdatesOverview::count($request->input('clearCache', false));
    }

    /**
     * Get updatable addons.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getUpdatableAddons()
    {
        return Addon::all()->filter->marketplaceSlug();
    }
}
