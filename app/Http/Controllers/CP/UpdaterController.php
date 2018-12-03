<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Addon;
use Illuminate\Http\Request;
use Facades\Statamic\Updater\UpdatesCount;

class UpdaterController extends CpController
{
    /**
     * Updates overview.
     */
    public function index()
    {
        // TODO: Setup permissions to see this.

        $updatableAddons = $this->getUpdatableAddons();

        if ($updatableAddons->isEmpty()) {
            return redirect()->route('statamic.cp.updater.product.index', ['statamic']);
        }

        // TODO: Proper view instead of this inline html.
        echo '<a href="' . route('statamic.cp.updater.product.index', 'statamic') . '">statamic core</a><br><br>';
        $updatableAddons->each(function ($addon) {
            echo '<a href="' . route('statamic.cp.updater.product.index', $addon) . '">' . $addon . '</a><br>';
        });
    }

    /**
     * Updates count.
     *
     * @param Request $request
     */
    public function count(Request $request)
    {
        // TODO: Setup permissions to see this.

        return UpdatesCount::get($request->input('clearCache', false));
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
