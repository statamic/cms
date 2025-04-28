<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\User;
use Statamic\Notifications\ElevatedSessionVerificationCode;

class ElevatedSessionController
{
    public function status(Request $request)
    {
        $user = User::current();

        $response = [
            'elevated' => $request->hasElevatedSession(),
            'expiry' => $request->getElevatedSessionExpiry(),
            'method' => $user->password() ? 'password_confirmation' : 'verification_code',
        ];

        if (! $request->hasElevatedSession() && $response['method'] === 'verification_code') {
            session()->put(
                key: 'statamic_elevated_session_verification_code',
                value: $verificationCode = Str::random(20)
            );

            $user->notify(new ElevatedSessionVerificationCode($verificationCode));
        }

        return $response;
    }

    public function showForm()
    {
        $user = User::current();
        $method = $user->password() ? 'password_confirmation' : 'verification_code';

        if ($method === 'verification_code') {
            session()->put(
                key: 'statamic_elevated_session_verification_code',
                value: $verificationCode = Str::random(20)
            );

            $user->notify(new ElevatedSessionVerificationCode($verificationCode));
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

        $method = $request->password ? 'password_confirmation' : 'verification_code';

        if ($request->password && ! Hash::check($request->password, $user->password())) {
            throw ValidationException::withMessages([
                'password' => [__('statamic::validation.current_password')],
            ]);
        }

        if ($request->verification_code && $request->verification_code !== $request->session()->get('statamic_elevated_session_verification_code')) {
            throw ValidationException::withMessages([
                'verification_code' => [__('statamic::validation.elevated_session_verification_code')],
            ]);
        }

        session()->elevate();

        return $request->wantsJson()
            ? $this->status($request)
            : redirect()->intended(cp_route('index'))->with('success', $method === 'password_confirmation' ? __('Password confirmed') : __('Code verified'));
    }
}
