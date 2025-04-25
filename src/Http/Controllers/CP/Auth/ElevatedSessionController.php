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
        return [
            'elevated' => $request->hasElevatedSession(),
            'expiry' => $request->getElevatedSessionExpiry(),
        ];
    }

    public function showForm()
    {
        return view('statamic::auth.confirm-password');
    }

    public function confirm(Request $request)
    {
        $user = User::current();

        $validated = $request->validate([
            'password' => 'required',
        ]);

        if (! Hash::check($validated['password'], $user->password())) {
            throw ValidationException::withMessages([
                'password' => [__('statamic::validation.current_password')],
            ]);
        }

        session()->elevate();

        return $request->wantsJson()
            ? $this->status($request)
            : redirect()->intended(cp_route('index'))->with('success', __('Password confirmed'));
    }
}
