<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Statamic\Auth\TwoFactor\CompleteTwoFactorAuthenticationSetup;
use Statamic\Auth\TwoFactor\ConfirmTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\EnableTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Facades\CP\Toast;
use Statamic\Facades\User;

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

        $viewData = [
            'qr' => $provider->getQrCodeSvg(),
            'secret_key' => $provider->getSecretKey(),
            'confirm_url' => cp_route('two-factor.confirm'),
        ];

        if ($request->wantsJson()) {
            return response()->json($viewData);
        }

        return view('statamic::auth.two-factor.setup', $viewData);
    }

    public function store(Request $request, ConfirmTwoFactorAuthentication $confirm, CompleteTwoFactorAuthenticationSetup $complete)
    {
        // confirm two factor
        $confirm(User::current(), $request->input('code', null));

        // complete it too - these were separate in the past, but they probably don't need to be any more.
        $complete(User::current());

        $viewData = [
            'recovery_codes' => json_decode(decrypt(User::current()->two_factor_recovery_codes), true),
        ];

        // todo: we don't actually need to return anything from this anymore
        if ($request->wantsJson()) {
            return response()->json($viewData);
        }

        // show recovery codes
        return view('statamic::auth.two-factor.recovery-codes', $viewData);
    }

    public function complete(Request $request, CompleteTwoFactorAuthenticationSetup $complete)
    {
        // complete the setup
        $complete(User::current());

        Toast::success(__('Two Factor Authentication has been set up.'));

        return redirect(cp_route('index'));
    }
}
