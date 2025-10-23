<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Auth\WebAuthn\Serializer;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Facades\User;
use Statamic\Facades\WebAuthn;

class PasskeyController
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

    public function create()
    {
        $options = WebAuthn::prepareAttestation(User::current());

        return app(Serializer::class)->normalize($options);
    }

    public function store(Request $request)
    {
        $credentials = $request->only(['id', 'rawId', 'response', 'type']);

        WebAuthn::validateAttestation(User::current(), $credentials, $request->name);

        return ['verified' => true];
    }

    public function destroy(Request $request, $id)
    {
        if (! $passkey = User::current()->passkeys()->get($id)) {
            abort(403);
        }

        $passkey->delete();

        return new JsonResponse([], 201);
    }
}
