<?php

namespace Statamic\Http\Controllers\CP;

use Facades\Statamic\Marketplace\Marketplace;

class ThemesController extends CpController
{
    public function index()
    {
        return Marketplace::themes();
    }
}
