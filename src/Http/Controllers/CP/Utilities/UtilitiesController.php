<?php

namespace Statamic\Http\Controllers\CP\Utilities;

use Illuminate\Http\Request;
use Statamic\Facades\Utility;
use Statamic\Http\Controllers\CP\CpController;

class UtilitiesController extends CpController
{
    public function index()
    {
        return view('statamic::utilities.index', [
            'utilities' => Utility::authorized(),
        ]);
    }

    public function show(Request $request)
    {
        $utility = Utility::find($request->segment(3));

        if ($view = $utility->view()) {
            return view($view, $utility->viewData($request));
        }

        throw new \Exception("Utility [{$utility->handle()}] has not been provided with an action or view.");
    }
}