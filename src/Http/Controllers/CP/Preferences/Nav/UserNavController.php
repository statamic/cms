<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav;

use Illuminate\Http\Request;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Controller;

class UserNavController extends Controller
{
    use Concerns\HasNavBuilder;

    protected function ignoreSaveAsOption()
    {
        return 'user';
    }

    protected function siteKey()
    {
        return 'nav.'.Site::selected()->handle();
    }

    public function edit()
    {
        $preferences = User::current()->getPreference($this->siteKey());

        $nav = Nav::build(
            preferences: $preferences ?: false,
            editing: true,
        );

        return $this->navBuilder($nav, [
            'title' => __('My Nav'),
            'updateUrl' => cp_route('preferences.nav.user.update'),
            'destroyUrl' => cp_route('preferences.nav.user.destroy'),
        ]);
    }

    public function update(Request $request)
    {
        $nav = $this->getUpdatedNav($request);

        User::current()->setPreference($this->siteKey(), $nav)->save();

        Nav::clearCachedUrls();

        $this->success(__('Saved'));

        return true;
    }

    public function destroy()
    {
        User::current()->removePreference($this->siteKey())->save();

        Nav::clearCachedUrls();

        return true;
    }
}
