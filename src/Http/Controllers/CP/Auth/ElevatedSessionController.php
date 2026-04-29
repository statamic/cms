<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Auth\ElevatedSessionController as BaseController;

class ElevatedSessionController extends BaseController
{
    public function status(Request $request)
    {
        $user = User::current();

        $response = [
            'elevated' => $hasElevatedSession = $request->hasElevatedSession(),
            'expiry' => $request->getElevatedSessionExpiry(),
            'method' => $method = $user->getElevatedSessionMethod(),
        ];

        if (! $hasElevatedSession && $method === 'verification_code') {
            session()->sendElevatedSessionVerificationCodeIfRequired();
        }

        return $response;
    }

    public function showForm(Request $request)
    {
        $user = User::current();

        if (($method = $user->getElevatedSessionMethod()) === 'verification_code') {
            session()->sendElevatedSessionVerificationCodeIfRequired();
        }

        return Inertia::render('auth/ConfirmPassword', [
            'outside' => false,
            'method' => $method,
            'status' => session('status'),
            'allowPasskey' => $method !== 'verification_code' && $user->passkeys()->isNotEmpty(),
            'submitUrl' => cp_route('elevated-session.confirm'),
            'resendUrl' => cp_route('elevated-session.resend-code'),
            'passkeyOptionsUrl' => cp_route('elevated-session.passkey-options'),
        ]);
    }

    protected function buildConfirmResponse(Request $request, $user)
    {
        $redirect = redirect()->intended(cp_route('index'));

        if ($request->wantsJson()) {
            return array_merge(
                $this->status($request),
                ['redirect' => $redirect->getTargetUrl()]
            );
        }

        return $redirect->with(
            'success',
            $user->getElevatedSessionMethod() === 'password_confirmation'
                ? __('Password confirmed')
                : __('Code verified')
        );
    }

    protected function throwValidationException(Request $request, array $errors): never
    {
        throw ValidationException::withMessages($errors);
    }
}
