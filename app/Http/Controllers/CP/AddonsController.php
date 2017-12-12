<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Addon;

/**
 * Controller for the addon area
 */
class AddonsController extends CpController
{
    public function index()
    {
        return view('addons.index', [
            'title' => 'Addons'
        ]);
    }

    public function get()
    {;
        $addons = Addon::all()->map(function ($addon) {
            return [
                'id'            => $addon->id(),
                'name'          => $addon->name(),
                'url'           => $addon->url(),
                'version'       => $addon->version(),
                'developer'     => $addon->developer(),
                'developer_url' => $addon->developerUrl(),
                'description'   => $addon->description(),
            ];
        })->values();

        return [
            'columns' => ['name', 'version', 'developer', 'description'],
            'items' => $addons,
            'pagination' => ['totalPages' => 1]
        ];
    }
}
