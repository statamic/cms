<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Inertia\Inertia;
use Statamic\Facades\OAuth;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\OAuth\Provider;
use Statamic\Statamic;

class OAuthController extends CpController
{
    public function index()
    {
        abort_unless(OAuth::enabled(), 404);

        $user = User::current();
        $redirect = parse_url(cp_route('oauth'))['path'];

        return Inertia::render('users/OAuth', [
            'providers' => OAuth::providers()
                ->reject(fn (Provider $provider) => $provider->isStateless())
                ->map(fn (Provider $provider) => [
                    'name' => $provider->name(),
                    'label' => $provider->label(),
                    'icon' => Statamic::svg('oauth/'.$provider->name()),
                    'connected' => $provider->isConnectedTo($user),
                    'connectUrl' => $provider->loginUrl().'?redirect='.$redirect,
                    'disconnectUrl' => cp_route('oauth.disconnect', $provider->name()),
                ])->values(),
        ]);
    }
}
