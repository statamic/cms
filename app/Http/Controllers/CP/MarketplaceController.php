<?php

namespace Statamic\Http\Controllers\CP;

use Facades\Statamic\Extend\Marketplace;

class MarketplaceController extends CpController
{
    public function addons()
    {
        return Marketplace::get();
    }
}
