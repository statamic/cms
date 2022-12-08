<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav\Concerns;

use Illuminate\Http\Request;
use Statamic\CP\Navigation\NavPreferencesConfig;
use Statamic\Facades\CP\Nav;
use Statamic\Http\Resources\CP\Nav\Nav as NavResource;

trait HasNavBuilder
{
    protected function navBuilder($nav = null, $props = [])
    {
        return view('statamic::nav.edit', array_merge([
            'title' => 'My Nav',
            'updateUrl' => cp_route('preferences.nav.user.update'),
            'destroyUrl' => cp_route('preferences.nav.user.destroy'),
            'nav' => NavResource::make($nav ?? Nav::build()),
        ], $props));
    }

    protected function getUpdatedNav(Request $request)
    {
        return NavPreferencesConfig::fromJavascript($request->tree);
    }
}
