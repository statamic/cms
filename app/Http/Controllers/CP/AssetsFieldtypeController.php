<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Fields\Fieldtypes\Assets as AssetsFieldtype;

class AssetsFieldtypeController extends CpController
{
    public function index(Request $request)
    {
        return (new AssetsFieldtype)
            ->getItemData($request->input('assets', []));
    }
}
