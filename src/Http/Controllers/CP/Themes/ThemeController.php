<?php

namespace Statamic\Http\Controllers\CP\Themes;

use Facades\Statamic\Marketplace\Marketplace;
use Statamic\Http\Controllers\CP\CpController;

class ThemeController extends CpController
{
    public function index()
    {
        return Marketplace::themes();
    }

    public function refresh()
    {
        Marketplace::clearThemesCache();

        return ['success' => true];
    }
}
