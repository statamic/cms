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
        $this->access('updater');

        // Todo: view
        echo '<a href="' . route('statamic.cp.updater.product.index', 'statamic') . '">statamic core</a><br><br>';

        Addon::all()->map->marketplaceSlug()->filter()->each(function ($addon) {
            echo '<a href="' . route('statamic.cp.updater.product.index', $addon) . '">' . $addon . '</a><br>';
        });

        // return redirect()->route('statamic.cp.updater.product.index', ['statamic']);
    }

    /**
     * Updates count.
     *
     * @param Request $request
     */
    public function count(Request $request)
    {
        $this->access('updater');

        return UpdatesCount::get($request->input('clearCache', false));
    }
}
