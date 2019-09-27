<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Support\Carbon;

class SessionTimeoutController extends CpController
{
    public function __invoke()
    {
        // If a "remember me" token was used to reauthenticate, the session would not be available yet since
        // it gets updated at the end of the request. We'll fallback to the current time. Users not using
        // remember me would have already been served a 403 error and wouldn't have got this far.
        $lastActivity = session('last_activity', now()->timestamp);

        return Carbon::createFromTimestamp($lastActivity)
            ->addMinutes(config('session.lifetime'))
            ->diffInSeconds();
    }
}
