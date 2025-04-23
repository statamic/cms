<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Statamic\Auth\TwoFactor\CompleteTwoFactorAuthenticationSetup;
use Statamic\Auth\TwoFactor\ConfirmTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\DisableTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\EnableTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\CP\Toast;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class TwoFactorAuthenticationController extends CpController
{
    public function enable(Request $request, $user, EnableTwoFactorAuthentication $enable)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        if (User::current()->id !== $user->id) {
            abort(403);
        }

        // We don't want to regenerate the QR code when there's an error in the session.
        if (! session()->get('errors')?->has('code')) {
            $enable($user);
        }

        return [
            'qr' => $user->twoFactorQrCodeSvg(),
            'secret_key' => $user->twoFactorSecretKey(),
            'confirm_url' => cp_route('users.two-factor.confirm', $user->id),
        ];
    }

    public function confirm(Request $request, $user, ConfirmTwoFactorAuthentication $confirm)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        if (User::current()->id !== $user->id) {
            abort(403);
        }

        $confirm($user, $request->input('code'));

        return [];
    }

    public function disable(Request $request, $user, DisableTwoFactorAuthentication $disable)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        if (! $request->user()->can('edit', $user)) {
            abort(403);
        }

        $disable($user);

        if ($request->user()->id === $user->id && $user->isTwoFactorAuthenticationRequired()) {
            return ['redirect' => cp_route('two-factor-setup')];
        }

        return ['redirect' => null];
    }
}
