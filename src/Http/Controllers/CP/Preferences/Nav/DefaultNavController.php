<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav;

use Illuminate\Http\Request;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Preference;
use Statamic\Http\Controllers\Controller;

class DefaultNavController extends Controller
{
    use Concerns\HasNavBuilder;

    protected function ignoreSaveAsOption()
    {
        return 'default';
    }

    public function edit()
    {
        $preferences = Preference::default()->get('nav');

        $nav = $preferences
            ? Nav::build($preferences, true)
            : Nav::buildWithoutPreferences(true);

        return $this->navBuilder($nav, [
            'title' => __('Default'),
            'updateUrl' => cp_route('preferences.nav.default.update'),
            'destroyUrl' => cp_route('preferences.nav.default.destroy'),
        ]);
    }

    public function update(Request $request)
    {
        $nav = $this->getUpdatedNav($request);

        if (! $nav) {
            return $this->destroy();
        }

        Preference::default()->set('nav', $nav)->save();

        $this->success(__('Saved'));

        return true;
    }

    public function destroy()
    {
        Preference::default()->remove('nav')->save();

        return true;
    }
}
