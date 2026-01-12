<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav;

use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Preference;
use Statamic\Facades\Role;
use Statamic\Facades\Site;
use Statamic\Http\Controllers\Controller;

use function Statamic\trans as __;

class RoleNavController extends Controller
{
    use Concerns\HasNavBuilder;

    protected $currentHandle;

    protected function ignoreSaveAsOption()
    {
        return $this->currentHandle;
    }

    protected function siteKey()
    {
        return 'nav.'.Site::selected()->handle();
    }

    public function edit($handle)
    {
        throw_unless($role = Role::find($handle), new NotFoundHttpException);

        $this->currentHandle = $handle;

        $preferences = $role->getPreference($this->siteKey()) ?? Preference::default()->get($this->siteKey());

        $nav = Nav::build(
            preferences: $preferences ?: false,
            editing: true,
        );

        return $this->navBuilder($nav, [
            'title' => __($role->title()),
            'updateUrl' => cp_route('preferences.nav.role.update', $role->handle()),
            'destroyUrl' => cp_route('preferences.nav.role.destroy', $role->handle()),
        ]);
    }

    public function update(Request $request, $handle)
    {
        throw_unless($role = Role::find($handle), new NotFoundHttpException);

        $nav = $this->getUpdatedNav($request);

        $role->setPreference($this->siteKey(), $nav)->save();

        Nav::clearCachedUrls();

        $this->success(__('Saved'));

        return true;
    }

    public function destroy($handle)
    {
        throw_unless($role = Role::find($handle), new NotFoundHttpException);

        $role->removePreference($this->siteKey())->save();

        Nav::clearCachedUrls();

        return true;
    }
}
