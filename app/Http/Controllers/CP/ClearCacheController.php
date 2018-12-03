<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;

class ClearCacheController extends CpController
{
    public function __invoke(Request $request)
    {
        return view('statamic::utilities.clear-cache');
    }
}
