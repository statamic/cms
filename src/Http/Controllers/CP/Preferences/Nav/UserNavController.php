<?php

namespace Statamic\Http\Controllers\CP\Preferences\Nav;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Controller;

class UserNavController extends Controller
{
    use Concerns\HasNavBuilder;

    protected function ignoreSaveAsOption()
    {
        return 'user';
    }

    public function edit()
    {
        return $this->navBuilder();
    }

    public function update(Request $request)
    {
        $nav = $this->getUpdatedNav($request);

        User::current()->setPreference('nav', $nav)->save();

        $this->success(__('Saved'));

        return true;
    }

    public function destroy()
    {
        User::current()->removePreference('nav')->save();

        return true;
    }
}
