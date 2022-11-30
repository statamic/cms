<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\CP\Navigation\NavPreferencesConfig;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Preference;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Controller;
use Statamic\Http\Resources\CP\Nav\Nav as NavResource;

class NavPreferencesController extends Controller
{
    public function edit()
    {
        return view('statamic::nav.edit', [
            'currentNav' => NavResource::make(Nav::build()),
            'defaultNav' => NavResource::make(Nav::build(false)),
            // 'roles' => Role::all()->map(fn ($role) => NavResource::make(Nav::build(
            //     $role->getPreference('nav')
            // ))),
        ]);
    }

    public function update(Request $request)
    {
        $target = $request->input('target', 'user');

        $nav = NavPreferencesConfig::fromJavascript($request->tree);

        if ($target === 'default') {
            Preference::default()->set('nav', $nav)->save();
        } elseif ($target === 'role') {
            // Role::find($request->role)->setPreference('nav', $nav)->save();
        } else {
            User::current()->setPreference('nav', $nav)->save();
        }

        return true;
    }
}
