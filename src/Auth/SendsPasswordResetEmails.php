<?php

namespace Statamic\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * A copy of Illuminate\Auth\SendsPasswordResetEmails.
 *
 * It was moved to laravel/ui, but we can't require it because it requires
 * Laravel 7 and would force us to drop support for Laravel <=6
 *
 * @see https://github.com/laravel/ui/blob/2.x/auth-backend/SendsPasswordResetEmails.php
 */
trait SendsPasswordResetEmails
{
    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }

    /**
     * Validate the email for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateEmail(Request $request)
    {
        // Workaround for `$request->validateWithBag()` not being available in older Laravel versions.
        try {
            $request->validate(['email' => 'required|email']);
        } catch (ValidationException $e) {
            $e->errorBag = 'user.forgot_password';

            throw $e;
        }
    }

    /**
     * Get the needed authentication credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only('email');
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        session()->flash('user.forgot_password.success', __(Password::RESET_LINK_SENT));

        $redirect = $request->has('_redirect')
            ? redirect($request->input('_redirect'))
            : back();

        return $request->wantsJson()
            ? new JsonResponse(['message' => trans($response)], 200)
            : $redirect->with('status', trans($response));
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        $redirect = $request->has('_error_redirect')
            ? redirect($request->input('_error_redirect'))
            : back();

        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                'email' => [trans($response)],
            ]);
        }

        return $redirect
            ->withInput($request->only('email'))
            ->withErrors(['email' => trans($response)], 'user.forgot_password');
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }
}
