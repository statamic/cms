<?php

namespace Statamic\Http\Controllers\User;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Statamic\Auth\WebAuthn\Serializer;
use Statamic\Facades\User;
use Statamic\Facades\WebAuthn;
use Statamic\Http\Controllers\Controller;

class PasskeyController extends Controller
{
    public function create()
    {
        $options = WebAuthn::prepareAttestation(User::current());

        return app(Serializer::class)->normalize($options);
    }

    public function store(Request $request)
    {
        $credentials = $request->only(['id', 'rawId', 'response', 'type']);

        WebAuthn::validateAttestation(User::current(), $credentials, $request->name ?? 'Passkey');

        return ['verified' => true];
    }

    public function destroy($id, Request $request)
    {
        if (! $passkey = User::current()->passkeys()->get($id)) {
            abort(403);
        }

        $passkey->delete();

        if ($request->wantsJson()) {
            return new JsonResponse([], 204);
        }

        return back()->with('success', __('Passkey deleted.'));
    }
}
