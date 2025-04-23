<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Statamic\Http\Controllers\TwoFactorChallengeController as Controller;
use Statamic\Http\Middleware\CP\RedirectIfAuthorized;
use Statamic\Support\Str;

class TwoFactorChallengeController extends Controller
{
    public function __construct()
    {
        //        $this->middleware('throttle:two-factor');
        $this->middleware(RedirectIfAuthorized::class);
    }

    protected function resetFormAction()
    {
        return cp_route('two-factor-challenge');
    }

    protected function redirectPath()
    {
        $cp = cp_route('index');
        $referer = request('referer');
        $referredFromCp = Str::startsWith($referer, $cp) && ! Str::startsWith($referer, $cp.'/auth/');

        return $referredFromCp ? $referer : $cp;
    }
}
