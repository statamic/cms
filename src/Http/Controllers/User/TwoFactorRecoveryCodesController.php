<?php

namespace Statamic\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\Auth\TwoFactor\GenerateNewRecoveryCodes;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class TwoFactorRecoveryCodesController extends CpController
{
    public function show(Request $request, $user)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        if (User::current()->id !== $user->id) {
            abort(403);
        }

        return ['recovery_codes' => $user->twoFactorRecoveryCodes()];
    }

    public function store(Request $request, $user, GenerateNewRecoveryCodes $generateRecoveryCodes)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        if (User::current()->id !== $user->id) {
            abort(403);
        }

        $generateRecoveryCodes($user);

        return ['recovery_codes' => $user->twoFactorRecoveryCodes()];
    }

    public function download(Request $request, $user)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        if (User::current()->id !== $user->id) {
            abort(403);
        }

        $filename = Str::slug(config('app.name')).'-recovery-codes.txt';

        $content = collect($user->twoFactorRecoveryCodes())->implode(PHP_EOL);

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
