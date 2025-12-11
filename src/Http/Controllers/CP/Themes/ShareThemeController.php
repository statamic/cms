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

        $response = Http::post('https://statamic.com/api/v1/marketplace/cp-themes', [
            'name' => $request->name,
            'colors' => $request->colors,
            'darkColors' => $request->darkColors,
        ]);

        return $response->json();
    }
}
