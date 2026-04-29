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

    protected function routes($user): array
    {
        return [
            'enable' => cp_route('users.two-factor.enable'),
            'recovery_codes' => [
                'show' => cp_route('users.two-factor.recovery-codes.show'),
                'generate' => cp_route('users.two-factor.recovery-codes.generate'),
                'download' => cp_route('users.two-factor.recovery-codes.download'),
            ],
        ];
    }
}
