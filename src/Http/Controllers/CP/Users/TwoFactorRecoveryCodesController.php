<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\Auth\TwoFactor\GenerateRecoveryCodes;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\User;

class TwoFactorRecoveryCodesController
{
    public function show(Request $request, $user)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        if (User::current()->id !== $user->id) {
            abort(403);
        }

        return [
            'recovery_codes' => json_decode(decrypt(User::current()->two_factor_recovery_codes), true),
        ];
    }

    public function store(Request $request, $user, GenerateRecoveryCodes $generateRecoveryCodes)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        if (User::current()->id !== $user->id) {
            abort(403);
        }

        $generateRecoveryCodes($user);

        return [
            'recovery_codes' => json_decode(decrypt($user->two_factor_recovery_codes), true),
        ];
    }

    public function download(Request $request, $user)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        if (User::current()->id !== $user->id) {
            abort(403);
        }

        $filename = Str::slug(config('app.name')).'-recovery-codes.txt';

        $content = collect(json_decode(decrypt($user->two_factor_recovery_codes), true))
            ->implode(PHP_EOL);

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
