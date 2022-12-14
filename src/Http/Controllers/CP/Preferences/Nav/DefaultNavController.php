<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav;

use Illuminate\Http\Request;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Preference;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Controller;
use Statamic\Statamic;

class DefaultNavController extends Controller
{
    use Concerns\HasNavBuilder;

    protected function ignoreSaveAsOption()
    {
        return 'default';
    }

    public function edit()
    {
        abort_unless(Statamic::pro() && User::current()->isSuper(), 403);

        $preferences = Preference::default()->get('nav');

        $nav = $preferences
            ? Nav::withHidden()->build($preferences)
            : Nav::withHidden()->buildWithoutPreferences();

        return $this->navBuilder($nav, [
            'title' => 'Global Default Nav',
            'updateUrl' => cp_route('preferences.nav.default.update'),
            'destroyUrl' => cp_route('preferences.nav.default.destroy'),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(Statamic::pro() && User::current()->isSuper(), 403);

        $nav = $this->getUpdatedNav($request);

        if (is_null($nav)) {
            return $this->destroy();
        }

        Preference::default()->set('nav', $nav)->save();

        return true;
    }

    public function destroy()
    {
        abort_unless(Statamic::pro() && User::current()->isSuper(), 403);

        Preference::default()->remove('nav')->save();

        return true;
    }
}
