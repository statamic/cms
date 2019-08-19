<?php

namespace Statamic\Http\Controllers\CP\Utilities;

use Statamic\Http\Controllers\CP\CpController;

class UtilitiesController extends CpController
{
    public function index()
    {
        return view('statamic::utilities.index');
    }
}