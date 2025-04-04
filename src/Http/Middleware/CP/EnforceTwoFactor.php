<?php

namespace Statamic\Http\Middleware\CP;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\Facades\TwoFactorUser;

class EnforceTwoFactor
{
    public function handle(Request $request, Closure $next)
    {
        // IS TWO FACTOR ENABLED?
        // we only need to do any further checks if two factor is enabled
        if (config('statamic.users.two_factor.enabled')) {
            // get the user
            $user = TwoFactorUser::get();

            // two factor setup (opt-in), or is two factor enforceable (mandatory) for this user?
            if ($user->two_factor_completed || TwoFactorUser::isTwoFactorEnforceable()) {

                // is two factor NOT set up?
                if (! $user->two_factor_completed) {
                    // go to setup
                    return redirect(cp_route('two-factor.setup'));
                }

                // when were we last challenged?
                $lastChallenge = TwoFactorUser::getLastChallenged();

                // do we use validity?
                // if so, we need to check if we have a challenge, and if it hasn't expired
                // if not, we just need to check we have a challenge
                if (config('statamic.users.two_factor.validity', null)) {
                    // if the request is a POST or PATCH, or a "cp/preferences/js" request, ignore it
                    // this is so that if you're in the middle of editing when it does in fact expire, you can still
                    // save changes. This may be a bit controversial, but any other requests would trigger the
                    // challenge, and provides the better UX. The next GET or DELETE would require the challenge.
                    //
                    // Ultimately, it probably doesn't matter if the challenge is a bit longer than the "validity"
                    // minutes as it doesn't need to be *exact* but at least reminding them roughly after that time in
                    // a non-obtrusive way for their workflow is a happy approach.
                    if (! in_array(strtoupper($request->method()), ['PATCH', 'POST']) &&
                        ! Str::startsWith($request->path(), config('statamic.cp.route').'/preferences/js')) {

                        // if we have no challenge token, it has expired
                        if (! $lastChallenge || Carbon::parse($lastChallenge)->addMinutes((int) config('statamic.users.two_factor.validity'))->isPast()) {
                            // not yet challenged, or expired, so yes, let's challenge
                            return redirect(cp_route('two-factor.challenge'))->with('two_factor_referer', $request->getRequestUri());
                        }
                    }
                } else {
                    // we don't care about expiry dates - we just need to know if we have been challenged at all
                    if (! $lastChallenge) {
                        return redirect(cp_route('two-factor.challenge'))->with('two_factor_referer', $request->getRequestUri());
                    }
                }
            }
        }

        // all good, continue
        return $next($request);
    }
}
