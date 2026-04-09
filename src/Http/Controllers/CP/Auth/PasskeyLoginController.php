<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\Request;
use Statamic\Facades\URL;
use Statamic\Http\Controllers\User\PasskeyLoginController as Controller;
use Statamic\Support\Str;

class PasskeyLoginController extends Controller
{
    protected function successRedirectUrl(Request $request): string
    {
        $referer = $request->input('referer');

        return Str::contains($referer, '/'.config('statamic.cp.route')) && ! URL::isExternalToApplication($referer)
            ? $referer
            : cp_route('index');
    }
}
