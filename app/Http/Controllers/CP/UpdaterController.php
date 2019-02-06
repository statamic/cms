<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Addon;
use Illuminate\Http\Request;
use Facades\Statamic\Updater\UpdatesOverview;

class UpdaterController extends CpController
{
    /**
     * Updates overview.
     */
    public function index()
    {
        $this->authorize('view updates');

        $updatableAddons = $this->getUpdatableAddons();

        if ($updatableAddons->isEmpty()) {
            return redirect()->route('statamic.cp.updater.products.index', ['statamic']);
        }

        // TODO: Proper view instead of this inline html.
        echo '<a href="' . route('statamic.cp.updater.products.index', 'statamic') . '">statamic core</a><br><br>';
        $updatableAddons->each(function ($addon) {
            echo '<a href="' . route('statamic.cp.updater.products.index', $addon) . '">' . $addon . '</a><br>';
        });
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
