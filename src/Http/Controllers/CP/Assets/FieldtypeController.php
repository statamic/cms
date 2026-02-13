<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Http\Request;
use Statamic\Fieldtypes\Assets\Assets as AssetsFieldtype;
use Statamic\Facades\Site;
use Statamic\Http\Controllers\CP\CpController;

class FieldtypeController extends CpController
{
    public function index(Request $request)
    {
        $site = $request->input('site');

        if (! $site && Site::multiEnabled()) {
            $site = Site::selected()->handle();
        }

        return (new AssetsFieldtype)
            ->getItemData($request->input('assets', []), $site);
    }
}
