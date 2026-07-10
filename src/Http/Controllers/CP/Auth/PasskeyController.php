<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Inertia\Inertia;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Facades\User;
use Statamic\Http\Controllers\User\PasskeyController as Controller;

class PasskeyController extends Controller
{
    public function index()
    {
        return Inertia::render('users/Passkeys', [
            'passkeys' => User::current()->passkeys()->map(function (Passkey $passkey) {
                return [
                    'name' => $passkey->name(),
                    'last_login' => ($login = $passkey->lastLogin()) ? $login->toAtomString() : null,
                    'delete_url' => cp_route('passkeys.destroy', ['id' => $passkey->id()]),
                ];
            })->values(),
            'createUrl' => cp_route('passkeys.create'),
            'storeUrl' => cp_route('passkeys.store'),
        ]);
    }
}
