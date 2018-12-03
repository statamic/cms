<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\API\Addon;
use Facades\Statamic\Extend\AddonInstaller;

class AddonsController extends CpController
{
    public function index()
    {
        // TODO: Setup permissions to see this.

        return view('statamic::addons.index', [
            'title' => 'Addons'
        ]);
    }

    public function install(Request $request)
    {
        return AddonInstaller::install($request->addon);
    }

    public function uninstall(Request $request)
    {
        return AddonInstaller::uninstall($request->addon);
    }
}
