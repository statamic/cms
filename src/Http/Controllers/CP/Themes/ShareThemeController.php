<?php

namespace Statamic\Http\Controllers\CP\Themes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Statamic\Http\Controllers\CP\CpController;

class ShareThemeController extends CpController
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'colors' => 'required|array',
            'darkColors' => 'required|array',
        ]);

        $response = Http::post('http://statamic.com.test/api/v1/marketplace/cp-themes', [
            'name' => $request->name,
            'colors' => $request->colors,
            'darkColors' => $request->darkColors,
            //            'key' => config('statamic.system.license_key'),
            //            'key' => '6kqv84g63gn5d1yx',
        ]);

        return $response->json();
    }
}
