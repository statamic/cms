<?php

namespace Statamic\Http\Controllers\CP\Users;

use Statamic\Http\Controllers\User\TwoFactorAuthenticationController as Controller;

class TwoFactorAuthenticationController extends Controller
{
    protected function confirmUrl($user)
    {
        return cp_route('users.two-factor.confirm', $user->id);
    }

    protected function setupUrlRedirect()
    {
        return cp_route('two-factor-setup');
    }
}
