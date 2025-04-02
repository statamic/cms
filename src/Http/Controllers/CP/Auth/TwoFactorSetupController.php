<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Statamic\Auth\TwoFactor\CompleteTwoFactorAuthenticationSetup;
use Statamic\Auth\TwoFactor\ConfirmTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\EnableTwoFactorAuthentication;
use Statamic\Facades\CP\Toast;
use Statamic\Facades\User;
use Statamic\Auth\TwoFactor\Google2FA;

class TwoFactorSetupController
{
    public function index(Request $request, Google2FA $provider, EnableTwoFactorAuthentication $enable)
    {
        // if we have an error for the code, then disable resetting the secret
        $resetSecret = true;
        if (optional(session()->get('errors'))->first('code')) {
            // we have tried a code, but failed
            $resetSecret = false;
        }

        // enable two factor, and optionally reset the user's code
        $enable(User::current(), $resetSecret);

        // show the setup view
        return view('statamic::auth.two-factor.setup', [
            'cancellable' => Arr::get(User::current()->two_factor, 'cancellable', false),
            'qr' => $provider->getQrCodeSvg(),
            'secret_key' => $provider->getSecretKey(),
        ]);
    }

    public function store(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        // confirm two factor
        $confirm(User::current(), $request->input('code', null));

        // show recovery codes
        return view('statamic::auth.two-factor.recovery-codes', [
            'recovery_codes' => json_decode(decrypt(User::current()->two_factor_recovery_codes), true),
        ]);
    }

    public function complete(Request $request, CompleteTwoFactorAuthenticationSetup $complete)
    {
        // complete the setup
        $complete(User::current());

        Toast::success(__('Two Factor Authentication has been set up.'));

        return redirect(cp_route('index'));
    }
}
