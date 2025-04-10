<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ElevatedSessionController
{
    public function index(Request $request)
    {
        $isElevated = session()->get("statamic_elevated_session_{$request->user()->id}") > now()->timestamp;
        $timeRemaining = $isElevated ? Carbon::parse(session()->get("statamic_elevated_session_{$request->user()->id}"))->diffInSeconds(absolute: true) : 0;

        return ['elevated' => $isElevated, 'time_remaining' => $timeRemaining];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required',
        ]);

        if (! Hash::check($validated['password'], $request->user()->password())) {
            throw ValidationException::withMessages([
                'password' => [__('statamic::validation.current_password')],
            ]);
        }

        session()->put(
            "statamic_elevated_session_{$request->user()->id}",
            now()->addMinutes(config('statamic.users.elevated_session_duration', 15))->timestamp
        );

        return $this->index($request);
    }
}
