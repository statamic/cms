<?php

namespace Statamic\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\Auth\TwoFactor\ConfirmTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\DisableTwoFactorAuthentication;
use Statamic\Auth\TwoFactor\EnableTwoFactorAuthentication;
use Statamic\Facades\URL;
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

        if (empty($user->two_factor_secret)) {
            $enable($user);
        }

        if ($request->expectsJson()) {
            return [
                'qr' => $user->twoFactorQrCodeSvg(),
                'secret_key' => $user->twoFactorSecretKey(),
                'confirm_url' => $this->confirmUrl(),
            ];
        }

        if (($redirect = $request->input('_redirect')) && ! URL::isExternalToApplication($redirect)) {
            return redirect($redirect);
        }

        if ($setupUrl = config('statamic.users.two_factor_setup_url')) {
            return redirect($setupUrl);
        }

        return back();
    }

    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $user = User::current();

        try {
            $confirm($user, $request->input('code'));
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                throw $e;
            }

            return $this->handleFormValidationError($request, $e, 'user.two_factor_setup');
        }

        if ($request->expectsJson()) {
            return [];
        }

        return $this->formSuccessRedirect($request, __('Two-factor authentication enabled.'), 'user.two_factor_setup');
    }

    public function disable(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $user = User::current();

        $disable($user);

        if ($request->expectsJson()) {
            if ($user->isTwoFactorAuthenticationRequired()) {
                return ['redirect' => $this->setupUrlRedirect()];
            }

            return ['redirect' => null];
        }

        if ($user->isTwoFactorAuthenticationRequired()) {
            return redirect($this->setupUrlRedirect())
                ->with('user.two_factor_disable.success', __('Two-factor authentication disabled.'));
        }

        return $this->formSuccessRedirect($request, __('Two-factor authentication disabled.'), 'user.two_factor_disable');
    }

    private function handleFormValidationError(Request $request, ValidationException $e, string $formName)
    {
        $errorRedirect = $request->input('_error_redirect');

        $redirect = $errorRedirect && ! URL::isExternalToApplication($errorRedirect)
            ? redirect($errorRedirect)
            : back();

        return $redirect->withInput()->withErrors($e->errors(), $formName);
    }

    private function formSuccessRedirect(Request $request, string $message, string $formName)
    {
        $successKey = "{$formName}.success";

        if (($redirect = $request->input('_redirect')) && ! URL::isExternalToApplication($redirect)) {
            return redirect($redirect)->with($successKey, $message);
        }

        return redirect()->intended(back()->getTargetUrl())->with($successKey, $message);
    }

    protected function confirmUrl()
    {
        return route('statamic.users.two-factor.confirm');
    }

    protected function setupUrlRedirect()
    {
        return config('statamic.users.two_factor_setup_url') ?? route('statamic.two-factor-setup');
    }
}
