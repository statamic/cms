<?php

namespace Statamic\Http\Controllers\CP\Users;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\Auth\TwoFactor\GenerateNewRecoveryCodes;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Middleware\CP\RequireElevatedSession;
use Statamic\Http\Middleware\RequireStatamicPro;

class TwoFactorRecoveryCodesController extends CpController
{
    public function __construct()
    {
        $this->middleware(RequireElevatedSession::class);
    }

    public function show(Request $request, $user)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        if (User::current()->id !== $user->id) {
            abort(403);
        }

        return ['recovery_codes' => $user->recoveryCodes()];
    }

    public function store(Request $request, $user, GenerateNewRecoveryCodes $generateRecoveryCodes)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        if (User::current()->id !== $user->id) {
            abort(403);
        }

        $generateRecoveryCodes($user);

        return ['recovery_codes' => $user->recoveryCodes()];
    }

    public function download(Request $request, $user)
    {
        throw_unless($user = User::find($user), new NotFoundHttpException);

        if (User::current()->id !== $user->id) {
            abort(403);
        }

        $filename = Str::slug(config('app.name')).'-recovery-codes.txt';

        $content = collect($user->recoveryCodes())->implode(PHP_EOL);

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
