<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav;

use Illuminate\Http\Request;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Preference;
use Statamic\Facades\Site;
use Statamic\Http\Controllers\Controller;

class DefaultNavController extends Controller
{
    use Concerns\HasNavBuilder;

    protected function ignoreSaveAsOption()
    {
        return 'default';
    }

    protected function siteKey()
    {
        return 'nav.'.Site::selected()->handle();
    }

    public function edit()
    {
        $preferences = Preference::default()->get($this->siteKey());

        $nav = Nav::build(
            preferences: $preferences ?: false,
            editing: true,
        );

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

        Preference::default()->set($this->siteKey(), $nav)->save();

        Nav::clearCachedUrls();

        $this->success(__('Saved'));

        return true;
    }

    public function destroy()
    {
        Preference::default()->remove($this->siteKey())->save();

        Nav::clearCachedUrls();

        return true;
    }
}
