<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav\Concerns;

use Illuminate\Http\Request;
use Statamic\CP\Navigation\NavTransformer;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Http\Resources\CP\Nav\Nav as NavResource;
use Statamic\Statamic;

trait HasNavBuilder
{
    protected function navBuilder($nav = null, $props = [])
    {
        return view('statamic::nav.edit', array_merge([
            'title' => 'My Nav',
            'updateUrl' => cp_route('preferences.nav.user.update'),
            'destroyUrl' => cp_route('preferences.nav.user.destroy'),
            'saveAsOptions' => $this->getSaveAsOptions()->values()->all(),
            'nav' => NavResource::make($nav ?? Nav::withHidden()->build()),
        ], $props));
    }

    protected function getUpdatedNav(Request $request, $allowOverriding = true)
    {
        return NavTransformer::fromVue($request->tree, $allowOverriding);
    }

    protected function getSaveAsOptions()
    {
        $canSaveAs = Statamic::pro() && User::current()->isSuper();

        $options = collect();

        if (! $canSaveAs) {
            return $options;
        }

        $options->put('default', [
            'label' => 'Save as Global Default Nav',
            'url' => cp_route('preferences.nav.default.update'),
        ]);

        Role::all()->each(function ($role) use (&$options) {
            $options->put($role->handle(), [
                'label' => 'Save as '.$role->title().' Role Nav',
                'url' => cp_route('preferences.nav.role.update', $role->handle()),
            ]);
        });

        $options->put('user', [
            'label' => 'Save as My Nav',
            'url' => cp_route('preferences.nav.user.update'),
        ]);

        if (method_exists($this, 'ignoreSaveAsOption')) {
            $options->forget($this->ignoreSaveAsOption());
        }

        return $options;
    }
}
