<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\Facades\Addon;

class AddonsController extends CpController
{
    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure addons');
    }

    public function index()
    {
        return view('statamic::addons.index', [
            'title' => __('Addons'),
            'addonCount' => Addon::all()->count(),
        ]);
    }
}
