<?php

namespace Statamic\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\Auth\TwoFactor\GenerateNewRecoveryCodes;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class TwoFactorRecoveryCodesController extends CpController
{
    public function show(Request $request)
    {
        return ['recovery_codes' => User::current()->twoFactorRecoveryCodes()];
    }

    public function store(Request $request, GenerateNewRecoveryCodes $generateRecoveryCodes)
    {
        $user = User::current();

        $generateRecoveryCodes($user);

        return ['recovery_codes' => $user->twoFactorRecoveryCodes()];
    }

    public function download(Request $request)
    {
        $user = User::current();

        $filename = Str::slug(config('app.name')).'-recovery-codes.txt';

        $content = collect($user->twoFactorRecoveryCodes())->implode(PHP_EOL);

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
