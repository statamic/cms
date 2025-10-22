<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Statamic\Auth\WebAuthn\Serializer;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Facades\User;
use Statamic\Support\Str;
use Webauthn;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\PublicKeyCredentialRequestOptions;

class PasskeyLoginController
{
    public function options($challenge = false)
    {
        $options = $this->publicKeyCredentialRequestOptions($challenge);

        return app(Serializer::class)->normalize($options);
    }

    private function publicKeyCredentialRequestOptions($challenge = false): PublicKeyCredentialRequestOptions
    {
        if (! $challenge) {
            $challenge = random_bytes(32);
            session()->put('webauthn.challenge', $challenge);
        }

        return PublicKeyCredentialRequestOptions::create(
            $challenge,
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED
        );
    }

    public function login(Request $request)
    {
        // https://webauthn-doc.spomky-labs.com/pure-php/authenticate-your-users#response-verification
        $publicKeyCredential = app(Serializer::class)->deserialize(
            $request->getContent(),
            Webauthn\PublicKeyCredential::class,
            'json'
        );

        if (! $publicKeyCredential->response instanceof Webauthn\AuthenticatorAssertionResponse) {
            throw new Exception(__('Invalid credentials'));
        }

        $user = User::find($publicKeyCredential->response->userHandle);

        // get from passkey repository
        /* @var Passkey $passkey */
        if (! $passkey = $user->passkeys()->firstWhere(fn (Passkey $key) => $key->id() == $publicKeyCredential->rawId)) {
            throw new Exception(__('No matching passkey found'));
        }

        $responseValidator = Webauthn\AuthenticatorAssertionResponseValidator::create(
            app(CeremonyStepManagerFactory::class)->requestCeremony()
        );

        $options = $this->publicKeyCredentialRequestOptions(
            session()->pull('webauthn.challenge')
        );

        $publicKeyCredentialSource = $responseValidator->check(
            $passkey->credential(),
            $publicKeyCredential->response,
            $options,
            $request->getHost(),
            $user->id()
        );

        // update passkey with latest data
        $passkey
            ->setCredential($publicKeyCredentialSource)
            ->setLastLogin(now());
        $passkey->save();

        Auth::login($passkey->user(), config('statamic.webauthn.remember_me', true));
        session()->elevate();
        session()->regenerate();

        if ($request->wantsJson()) {
            return new JsonResponse([
                'redirect' => $this->successRedirectUrl(),
            ], 200);
        }

        return redirect()->to($this->successRedirectUrl());
    }

    private function successRedirectUrl()
    {
        $referer = request('referer');

        return Str::contains($referer, '/'.config('statamic.cp.route')) ? $referer : cp_route('index');
    }
}
