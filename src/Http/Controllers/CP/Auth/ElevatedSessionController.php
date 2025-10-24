<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Statamic\Auth\WebAuthn\Serializer;
use Statamic\Facades\User;
use Statamic\Facades\WebAuthn;

class ElevatedSessionController
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

    public function options()
    {
        $options = WebAuthn::prepareAssertion();

        return app(Serializer::class)->normalize($options);
    }

    public function showForm()
    {
        $user = User::current();

        if (($method = $user->getElevatedSessionMethod()) === 'verification_code') {
            session()->sendElevatedSessionVerificationCodeIfRequired();
        }

        return Inertia::render('auth/ConfirmPassword', [
            'method' => $method,
            'status' => session('status'),
            'allowPasskey' => $method !== 'verification_code' && $user->passkeys()->isNotEmpty(),
            'submitUrl' => cp_route('elevated-session.confirm'),
            'resendUrl' => cp_route('elevated-session.resend-code'),
            'passkeyOptionsUrl' => cp_route('elevated-session.passkey-options'),
        ]);
    }

    public function confirm(Request $request)
    {
        $user = User::current();

        $request->validate([
            'password' => 'required_without_all:verification_code,id',
            'verification_code' => 'required_without_all:password,id',
            'id' => 'required_without_all:password,verification_code',
        ], [
            'password.required_without_all' => __('statamic::validation.required'),
            'verification_code.required_without_all' => __('statamic::validation.required'),
            'id.required_without_all' => __('statamic::validation.required'),
        ]);

        if ($request->password && ! Hash::check($request->password, $user->password())) {
            throw ValidationException::withMessages([
                'password' => [__('statamic::validation.current_password')],
            ]);
        }

        if ($request->verification_code && $request->verification_code !== $request->getElevatedSessionVerificationCode()) {
            throw ValidationException::withMessages([
                'verification_code' => [__('statamic::validation.elevated_session_verification_code')],
            ]);
        }

        if ($request->id) {
            $credentials = $request->only(['id', 'rawId', 'response', 'type']);
            WebAuthn::validateAssertion($user, $credentials);
        }

        session()->elevate();

        $redirect = redirect()->intended(cp_route('index'));

        return $request->wantsJson()
            ? array_merge($this->status($request), ['redirect' => $redirect->getTargetUrl()])
            : $redirect->with('success', $user->getElevatedSessionMethod() === 'password_confirmation' ? __('Password confirmed') : __('Code verified'));
    }

    public function resendCode()
    {
        if (User::current()->getElevatedSessionMethod() !== 'verification_code') {
            throw ValidationException::withMessages(['method' => 'Resend code is only available for verification code method']);
        }

        session()->sendElevatedSessionVerificationCode();

        return back()->with('success', __('statamic::messages.elevated_session_verification_code_sent'));
    }
}
