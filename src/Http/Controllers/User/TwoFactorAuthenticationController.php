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

        if (! session()->get('errors')?->has('code')) {
            $enable($user);
        }

        return [
            'qr' => $user->twoFactorQrCodeSvg(),
            'secret_key' => $user->twoFactorSecretKey(),
            'confirm_url' => $this->confirmUrl(),
        ];
    }

    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        $user = User::current();

        try {
            $confirm($user, $request->input('code'));
        } catch (ValidationException $e) {
            if (! $request->has('_redirect')) {
                throw $e;
            }

            return $this->handleFormValidationError($request, $e);
        }

        if (! $request->has('_redirect')) {
            return [];
        }

        return $this->formSuccessRedirect($request, __('Two-factor authentication enabled.'));
    }

    public function disable(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $user = User::current();

        $disable($user);

        if (! $request->has('_redirect') && ! $request->has('_setup_url')) {
            if ($user->isTwoFactorAuthenticationRequired()) {
                return ['redirect' => $this->setupUrlRedirect()];
            }

            return ['redirect' => null];
        }

        if ($user->isTwoFactorAuthenticationRequired()) {
            $this->storeSetupUrlInSession($request);

            return redirect($this->getSetupUrl($request))
                ->with('success', __('Two-factor authentication disabled.'));
        }

        return $this->formSuccessRedirect($request, __('Two-factor authentication disabled.'));
    }

    private function getSetupUrl(Request $request): string
    {
        $setupUrl = $request->input('_setup_url');

        if ($setupUrl && ! URL::isExternalToApplication($setupUrl)) {
            return $setupUrl;
        }

        return $this->setupUrlRedirect();
    }

    private function storeSetupUrlInSession(Request $request): void
    {
        $setupUrl = $request->input('_setup_url');

        if ($setupUrl && ! URL::isExternalToApplication($setupUrl)) {
            $request->session()->put('login.two_factor_setup_url', $setupUrl);
        }
    }

    private function handleFormValidationError(Request $request, ValidationException $e)
    {
        $errorRedirect = $request->input('_error_redirect');

        $redirect = $errorRedirect && ! URL::isExternalToApplication($errorRedirect)
            ? redirect($errorRedirect)
            : back();

        return $redirect->withInput()->withErrors($e->errors());
    }

    private function formSuccessRedirect(Request $request, string $message)
    {
        if ($redirect = $request->input('_redirect')) {
            if (! URL::isExternalToApplication($redirect)) {
                return redirect($redirect)->with('success', $message);
            }
        }

        if ($loginRedirect = $request->session()->pull('login.redirect')) {
            if (! URL::isExternalToApplication($loginRedirect)) {
                return redirect($loginRedirect)->with('success', $message);
            }
        }

        return redirect(route('statamic.site'))->with('success', $message);
    }

    protected function confirmUrl()
    {
        return route('statamic.users.two-factor.confirm');
    }

    protected function setupUrlRedirect()
    {
        return route('statamic.two-factor-setup');
    }
}
