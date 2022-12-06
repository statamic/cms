<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav;

use Illuminate\Http\Request;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Preference;
use Statamic\Facades\User;
use Statamic\Http\Resources\CP\Nav\Nav as NavResource;

class DefaultNavController extends NavController
{
    public function edit($handle = null)
    {
        abort_unless(User::current()->isSuper(), 403);

        return $this->navBuilder([
            'title' => 'Global Default Nav',
            'updateUrl' => cp_route('preferences.nav.default.update'),
            'currentNav' => NavResource::make(Nav::build()),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(User::current()->isSuper(), 403);

        $nav = $this->getUpdatedNav($request);

        Preference::default()->set('nav', $nav)->save();

        return true;
    }
}
