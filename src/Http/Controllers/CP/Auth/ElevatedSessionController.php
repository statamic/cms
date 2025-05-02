<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\User;

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

    public function showForm()
    {
        $user = User::current();

        if (($method = $user->getElevatedSessionMethod()) === 'verification_code') {
            session()->sendElevatedSessionVerificationCodeIfRequired();
        }

        return view('statamic::auth.confirm-password', [
            'method' => $method,
        ]);
    }

    public function confirm(Request $request)
    {
        $user = User::current();

        $request->validate([
            'password' => 'required_without:verification_code',
            'verification_code' => 'required_without:password',
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

        session()->elevate();

        return $request->wantsJson()
            ? $this->status($request)
            : redirect()->intended(cp_route('index'))->with('success', $user->getElevatedSessionMethod() === 'password_confirmation' ? __('Password confirmed') : __('Code verified'));
    }

    public function resendCode()
    {
        if (User::current()->getElevatedSessionMethod() !== 'verification_code') {
            throw new \LogicException('Resend code is only available for verification code method');
        }

        session()->sendElevatedSessionVerificationCode();

        return back()->with('success', __('statamic::messages.elevated_session_verification_code_sent'));
    }
}
