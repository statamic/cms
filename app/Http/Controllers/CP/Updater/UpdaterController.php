<?php

namespace Statamic\Http\Controllers\CP\Updater;

use Statamic\Statamic;
use Statamic\API\Addon;
use Illuminate\Http\Request;
use Statamic\Updater\Changelog;
use Facades\Statamic\Updater\UpdatesOverview;
use Statamic\Http\Controllers\CP\CpController;

class UpdaterController extends CpController
{
    /**
     * Updates overview.
     */
    public function index()
    {
        $this->authorize('view updates');

        $addons = $this->getUpdatableAddons();
        $statamic = Changelog::product(Statamic::CORE_REPO);

        return view('statamic::updater.index', ['addons' => $addons]);
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
        return Addon::all()->map->marketplaceSlug()->filter();
    }
}
