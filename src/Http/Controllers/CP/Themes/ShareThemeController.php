<?php

namespace Statamic\Http\Controllers\CP\Themes;

use Facades\Statamic\Marketplace\Client;
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

        $url = Client::requestEndpoint('cp-themes');

        $response = Http::post($url, [
            'name' => $request->name,
            'colors' => $request->colors,
            'darkColors' => $request->darkColors,
        ]);

        return $response->json();
    }
}
