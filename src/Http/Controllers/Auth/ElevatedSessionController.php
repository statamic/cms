<?php

namespace Statamic\Http\Controllers\Auth;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Statamic\Auth\WebAuthn\Serializer;
use Statamic\Facades\User;
use Statamic\Facades\WebAuthn;
use Statamic\Http\Controllers\Controller;
use Statamic\Http\Requests\Auth\ElevatedSessionConfirmationRequest;

class ElevatedSessionController extends Controller
{
    public function showForm(Request $request)
    {
        if ($customUrl = config('statamic.users.elevated_sessions_url')) {
            return redirect()->to($customUrl);
        }

        $user = User::current();
        $method = $user->getElevatedSessionMethod();

        if ($method === 'verification_code') {
            session()->sendElevatedSessionVerificationCodeIfRequired();
        }

        return Inertia::render('auth/ConfirmPassword', [
            'outside' => true,
            'method' => $method,
            'allowPasskey' => $method !== 'verification_code' && $user->passkeys()->isNotEmpty(),
            'status' => session('status'),
            'submitUrl' => route('statamic.elevated-session.confirm'),
            'resendUrl' => route('statamic.elevated-session.resend-code'),
            'passkeyOptionsUrl' => route('statamic.elevated-session.passkey-options'),
        ]);
    }

    public function confirm(ElevatedSessionConfirmationRequest $request)
    {
        $user = User::current();

        $this->validatePasswordConfirmation($request, $user);
        $this->validateVerificationCodeConfirmation($request);
        $this->validatePasskeyConfirmation($request, $user);

        session()->elevate();

        return $this->buildConfirmResponse($request, $user);
    }

    protected function buildConfirmResponse(Request $request, $user)
    {
        $message = $user->getElevatedSessionMethod() === 'password_confirmation'
            ? __('Password confirmed')
            : __('Code verified');

        $redirect = redirect()->intended(route('statamic.site'));

        if ($request->wantsJson()) {
            return response()->json([
                'elevated' => true,
                'expiry' => $request->getElevatedSessionExpiry(),
                'redirect' => $redirect->getTargetUrl(),
            ]);
        }

        return $request->inertia()
            ? Inertia::location($redirect->getTargetUrl())
            : $redirect->with('success', $message);
    }

    public function options()
    {
        $options = WebAuthn::prepareAssertion();

        return app(Serializer::class)->normalize($options);
    }

    public function resendCode()
    {
        if (User::current()->getElevatedSessionMethod() !== 'verification_code') {
            throw ValidationException::withMessages([
                'method' => __('statamic::validation.elevated_session_resend_code_unavailable'),
            ]);
        }

        session()->sendElevatedSessionVerificationCode();

        return back()->with('status', __('statamic::messages.elevated_session_verification_code_sent'));
    }

    private function validatePasswordConfirmation(Request $request, $user): void
    {
        if (! $request->filled('password')) {
            return;
        }

        if (Hash::check($request->password, $user->password())) {
            return;
        }

        $this->throwValidationException($request, [
            'password' => [__('statamic::validation.current_password')],
        ]);
    }

    private function validateVerificationCodeConfirmation(Request $request): void
    {
        if (! $request->filled('verification_code')) {
            return;
        }

        $verificationCode = $request->verification_code;
        $storedVerificationCode = $request->getElevatedSessionVerificationCode();

        if (
            is_string($verificationCode)
            && is_string($storedVerificationCode)
            && hash_equals($storedVerificationCode, $verificationCode)
        ) {
            return;
        }

        $this->throwValidationException($request, [
            'verification_code' => [__('statamic::validation.elevated_session_verification_code')],
        ]);
    }

    protected function throwValidationException(Request $request, array $errors): never
    {
        if ($request->wantsJson() || $request->inertia()) {
            throw ValidationException::withMessages($errors);
        }

        throw new HttpResponseException(
            back()->withInput()->withErrors($errors, 'user.elevated_session')
        );
    }

    private function validatePasskeyConfirmation(Request $request, $user): void
    {
        if (! $request->filled('id')) {
            return;
        }

        $credentials = $request->only(['id', 'rawId', 'response', 'type']);
        WebAuthn::validateAssertion($user, $credentials);
    }
}
