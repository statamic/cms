<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\API\Addon;
use Facades\Statamic\Extend\AddonInstaller;

class AddonsController extends CpController
{
    public function __construct()
    {
        // Temporarily using PackToTheFuture to fake version tags until we get this hooked up to statamic/cms.
        require(base_path('vendor/statamic/cms/tests/Fakes/Composer/Package/PackToTheFuture.php'));
    }

    public function index()
    {
        return view('statamic::addons.index', [
            'title' => 'Addons'
        ]);
    }

    public function install(Request $request)
    {
        // if ($changelog = Changelog::product($request->addon)->latest()) {

        // }
        // \Tests\Fakes\Composer\Package\PackToTheFuture::setAddon($request->addon, '1.0.0'); // Temp!

        return AddonInstaller::install($request->addon);
    }

    public function uninstall(Request $request)
    {
        \Tests\Fakes\Composer\Package\PackToTheFuture::setAddon($request->addon, '1.0.0'); // Temp!

        return AddonInstaller::uninstall($request->addon);
    }
}
