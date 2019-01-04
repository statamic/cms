<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Support\Carbon;

class SessionTimeoutController extends CpController
{
    public function __invoke()
    {
        return Carbon::createFromTimestamp(session('last_activity'))
            ->addMinutes(config('session.lifetime'))
            ->diffInSeconds();
    }
}
