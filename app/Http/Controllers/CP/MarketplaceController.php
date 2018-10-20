<?php

namespace Statamic\Http\Controllers\CP;

use Facades\Statamic\Extend\Marketplace;
use Illuminate\Http\Request;

class MarketplaceController extends CpController
{
    public function addons(Request $request)
    {
        return Marketplace::filter($request->filter)->search($request->q)->paginate(30);
    }
}
