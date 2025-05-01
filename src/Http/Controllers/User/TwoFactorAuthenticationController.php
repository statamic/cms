<?php

namespace Statamic\Http\Controllers\User;

use Illuminate\Http\Request;
use Statamic\Auth\TwoFactor\ConfirmTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\DisableTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\EnableTwoFactorAuthentication;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class TwoFactorAuthenticationController extends CpController
{
    public function enable(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $user = User::current();

        if ($user->hasEnabledTwoFactorAuthentication()) {
            abort(403);
        }

        // We don't want to regenerate the QR code when there's an error in the session.
        if (! session()->get('errors')?->has('code')) {
            $enable($user);
        }

        return [
            'qr' => $user->twoFactorQrCodeSvg(),
            'secret_key' => $user->twoFactorSecretKey(),
            'confirm_url' => $this->confirmUrl($user),
        ];
    }

    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $user = User::current();

        $confirm($user, $request->input('code'));

        return [];
    }

    public function disable(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $user = User::current();

        $disable($user);

        if ($user->isTwoFactorAuthenticationRequired()) {
            return ['redirect' => $this->setupUrlRedirect()];
        }

        return ['redirect' => null];
    }

    protected function confirmUrl($user)
    {
        return route('statamic.users.two-factor.confirm', $user->id);
    }

    protected function setupUrlRedirect()
    {
        return route('statamic.two-factor-setup');
    }
}
