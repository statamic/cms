<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Statamic\Facades\User;

class ElevatedSessionController
{
    public function index(Request $request)
    {
        return [
            'elevated' => $request->hasElevatedSession(),
            'expiry' => $request->getElevatedSessionExpiry(),
        ];
    }

    public function store(Request $request)
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

        return $this->index($request);
    }
}
