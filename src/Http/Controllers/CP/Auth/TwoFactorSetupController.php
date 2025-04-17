<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Statamic\Auth\TwoFactor\CompleteTwoFactorAuthenticationSetup;
use Statamic\Auth\TwoFactor\ConfirmTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\EnableTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Facades\CP\Toast;
use Statamic\Facades\User;

class TwoFactorSetupController
{
    public function index(Request $request, TwoFactorAuthenticationProvider $provider, EnableTwoFactorAuthentication $enable)
    {
        $user = $request->user();

        // if we have an error for the code, then disable resetting the secret
        $resetSecret = true;
        if (optional(session()->get('errors'))->first('code')) {
            // we have tried a code, but failed
            $resetSecret = false;
        }

        $enable(User::current(), $resetSecret);

        $viewData = [
            'qr' => $user->twoFactorQrCodeSvg(),
            'secret_key' => $user->twoFactorSecretKey(),
            'confirm_url' => cp_route('two-factor.confirm'),
        ];

        if ($request->wantsJson()) {
            return response()->json($viewData);
        }

        return view('statamic::auth.two-factor.setup', $viewData);
    }

    public function store(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $confirm(User::current(), $request->input('code'));

        $viewData = [
            'complete_url' => cp_route('two-factor.complete'),
        ];

        if ($request->wantsJson()) {
            return response()->json($viewData);
        }

        // todo: we won't need this after refactoring the login process
        return view('statamic::auth.two-factor.recovery-codes', $viewData);
    }

    public function complete(Request $request, CompleteTwoFactorAuthenticationSetup $complete)
    {
        $complete(User::current());

        Toast::success(__('Two Factor Authentication has been set up.'));

        return redirect(cp_route('index'));
    }
}
