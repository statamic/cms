<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\TwoFactorChallengeController as Controller;
use Statamic\Http\Middleware\CP\HandleInertiaRequests;
use Statamic\Http\Middleware\CP\RedirectIfAuthorized;
use Statamic\Support\Str;

class TwoFactorChallengeController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:two-factor');
        $this->middleware(HandleInertiaRequests::class)->except('store');
        $this->middleware(RedirectIfAuthorized::class);
    }

    protected function formAction()
    {
        return cp_route('two-factor-challenge');
    }

    protected function redirectPath(Request $request)
    {
        $cp = cp_route('index');
        $referer = $request->input('referer');
        $referredFromCp = Str::startsWith($referer, $cp) && ! Str::startsWith($referer, $cp.'/auth/');

        return $referredFromCp ? $referer : $cp;
    }

    protected function failedRedirectPath()
    {
        return cp_route('two-factor-challenge');
    }
}
