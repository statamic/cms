<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Addon;
use Facades\Statamic\Composer\AddonInstaller;

class AddonsController extends CpController
{
    public function index()
    {
        return view('statamic::addons.index', [
            'title' => 'Addons'
        ]);
    }

    // public function index()
    // {
    //     return Marketplace::approvedAddons();
    // }

    public function installed()
    {
        return AddonInstaller::installed();
    }

    public function install(Request $request)
    {
        return AddonInstaller::install($request->addon);
    }

    public function uninstall(Request $request)
    {
        return AddonInstaller::uninstall($request->addon);
    }

    // Not sure if needed in this form?
    // public function get()
    // {
    //     $addons = Addon::all()->map(function ($addon) {
    //         return [
    //             'id'            => $addon->id(),
    //             'name'          => $addon->name(),
    //             'url'           => $addon->url(),
    //             'version'       => $addon->version(),
    //             'developer'     => $addon->developer(),
    //             'developer_url' => $addon->developerUrl(),
    //             'description'   => $addon->description(),
    //         ];
    //     })->values();

    //     return [
    //         'columns' => ['name', 'version', 'developer', 'description'],
    //         'items' => $addons,
    //         'pagination' => ['totalPages' => 1]
    //     ];
    // }
}
