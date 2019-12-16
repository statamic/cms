<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Fieldtypes\Assets\Assets as AssetsFieldtype;

class FieldtypeController extends CpController
{
    public function index(Request $request)
    {
        return (new AssetsFieldtype)
            ->getItemData($request->input('assets', []));
    }
}
