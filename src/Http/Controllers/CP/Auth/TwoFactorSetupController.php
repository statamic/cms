<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\TwoFactorSetupController as Controller;
use Statamic\Support\Str;

class TwoFactorSetupController extends Controller
{
    public function __construct(Request $request)
    {
        $this->middleware('statamic.cp.authenticated');
    }

    protected function redirectPath()
    {
        $cp = cp_route('index');
        $referer = request('referer');
        $referredFromCp = Str::startsWith($referer, $cp) && ! Str::startsWith($referer, $cp.'/auth/');

        return $referredFromCp ? $referer : $cp;
    }
}
