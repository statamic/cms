<?php

namespace Statamic\Http\Controllers\CP;

use Facades\Statamic\Updater\UpdatesCount;
use Illuminate\Http\Request;
use Statamic\API\Addon;

class UpdaterController extends CpController
{
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

    public function count(Request $request)
    {
        $this->access('updater');

        return UpdatesCount::get($request->input('clearCache', false));
    }
}
