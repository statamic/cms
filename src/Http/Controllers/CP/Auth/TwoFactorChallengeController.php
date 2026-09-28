<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Statamic\Http\Controllers\TwoFactorChallengeController as Controller;
use Statamic\Http\Middleware\CP\HandleInertiaRequests;
use Statamic\Http\Middleware\CP\RedirectIfAuthorized;

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

    protected function defaultRedirectPath(): string
    {
        return cp_route('index');
    }

    protected function failedRedirectPath()
    {
        return cp_route('two-factor-challenge');
    }
}
