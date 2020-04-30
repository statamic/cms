<?php

namespace Statamic\Http\View\Composers;

use Illuminate\View\View;
use Statamic\Facades\OAuth;
use Statamic\Facades\User;

class SessionExpiryComposer
{
    const VIEWS = ['statamic::partials.session-expiry'];

    public function compose(View $view)
    {
        $view->with([
            'email' => User::current()->email(),
            'lifetime' => config('session.lifetime') * 60,
            'warnAt' => 60,
            'oauth' => $this->oauth(),
        ]);
    }

    protected function oauth()
    {
        if (! $provider = session('oauth-provider')) {
            return null;
        }

        return OAuth::provider($provider)->toArray();
    }
}
