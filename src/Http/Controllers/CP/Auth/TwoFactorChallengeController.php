<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Statamic\Auth\TwoFactor\ChallengeTwoFactorAuthentication;
use Statamic\Facades\User;

class TwoFactorChallengeController
{
    use Concerns\GetsReferrerUrl;

    public function index(Request $request)
    {
        // if we have a failed attempt, increase the attempts - if needed, the reload will redirect to the locked screen
        if (optional(session()->get('errors'))->first('code') || optional(session()->get('errors'))->first('recovery_code')) {
            $request->session()->put('statamic_two_factor_attempts',
                $request->session()->get('statamic_two_factor_attempts', 0) + 1);
        }

        // if we have exceeded the number of attempts, lock the account
        if ($request->session()->get('statamic_two_factor_attempts', 0) >= config('statamic.users.two_factor.attempts')) {
            // block the account
            User::current()->set('two_factor_locked', true)->save();

            // redirect to the locked view
            return redirect(cp_route('two-factor.locked'));
        }

        // if we have a referrer URL, set it
        if ($referrer = $this->getReferrerUrl($request)) {
            // if we are not null, let's set it (this way it won't overwrite on failed attempts)
            if ($referrer) {
                $request->session()->put('statamic_two_factor_referrer', $referrer);
            }
        }

        // show the challenge view
        return view('statamic::auth.two-factor.challenge', [
            'mode' => $request->session()->get('mode', 'code'),
        ]);
    }

    public function store(Request $request, ChallengeTwoFactorAuthentication $challenge)
    {
        // set the mode
        $mode = $request->get('mode', 'code');
        $request->session()->flash('mode', $mode);

        // do the challenge
        $challenge(User::current(), $mode, $request->input($mode, null));

        // forget the attempts count
        $request->session()->forget('statamic_two_factor_attempts');

        // get the redirect route, or the referrer if we set one
        $route = cp_route('index');
        if ($referrer = session()->pull('statamic_two_factor_referrer', null)) {
            $route = $referrer;
        }

        return redirect($route);
    }
}
