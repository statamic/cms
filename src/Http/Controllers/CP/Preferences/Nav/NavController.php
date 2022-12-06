<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav;

use Illuminate\Http\Request;
use Statamic\CP\Navigation\NavPreferencesConfig;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Controller;
use Statamic\Http\Resources\CP\Nav\Nav as NavResource;

class NavController extends Controller
{
    public function index()
    {
        abort_unless(User::current()->isSuper(), 403);

        return view('statamic::nav.index');
    }

    public function edit($handle = null)
    {
        return $this->navBuilder();
    }

    public function update(Request $request)
    {
        $nav = $this->getUpdatedNav($request);

        User::current()->setPreference('nav', $nav)->save();

        return true;
    }

    protected function navBuilder($props = [])
    {
        return view('statamic::nav.edit', array_merge([
            'title' => 'My Nav',
            'updateUrl' => cp_route('preferences.nav.update'),
            'currentNav' => NavResource::make(Nav::build()),
            'defaultNav' => NavResource::make(Nav::buildDefault()),
        ], $props));
    }

    protected function getUpdatedNav(Request $request)
    {
        return NavPreferencesConfig::fromJavascript($request->tree);
    }
}
