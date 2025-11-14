<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav\Concerns;

use Illuminate\Http\Request;
use Inertia\Inertia;
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
        $indexUrl = Statamic::pro() && User::current()->can('manage preferences')
            ? cp_route('preferences.nav.index')
            : false;

        return Inertia::render('preferences/nav/Edit', array_merge([
            'title' => __('My Nav'),
            'indexUrl' => $indexUrl,
            'updateUrl' => cp_route('preferences.nav.user.update'),
            'destroyUrl' => cp_route('preferences.nav.user.destroy'),
            'saveAsOptions' => $this->getSaveAsOptions()->values()->all(),
            'nav' => NavResource::make($nav ?? Nav::build(true, true))->resolve(request()),
        ], $props));
    }

    protected function getUpdatedNav(Request $request)
    {
        return NavTransformer::fromVue($request->tree);
    }

    protected function getSaveAsOptions()
    {
        $canSaveAs = Statamic::pro() && User::current()->isSuper();

        $options = collect();

        if (! $canSaveAs) {
            return $options;
        }

        $options->put('default', [
            'label' => __('Default'),
            'url' => cp_route('preferences.nav.default.update'),
            'icon' => 'earth',
        ]);

        Role::all()->each(function ($role) use (&$options) {
            $options->put($role->handle(), [
                'label' => $role->title(),
                'url' => cp_route('preferences.nav.role.update', $role->handle()),
                'icon' => 'shield-key',
            ]);
        });

        $options->put('user', [
            'label' => __('My Nav'),
            'url' => cp_route('preferences.nav.user.update'),
            'icon' => 'user',
        ]);

        if (method_exists($this, 'ignoreSaveAsOption')) {
            $options->forget($this->ignoreSaveAsOption());
        }

        return $options;
    }
}
