<?php

namespace Statamic\Http\Controllers;

use Statamic\Facades\User;
use Statamic\Auth\PasswordReset;
use Statamic\Auth\UserRegistrar;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Auth;
use Statamic\Contracts\Auth\User as UserContract;

class UserController extends Controller
{
    private $request;

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $loggedIn = Auth::attempt(
            $request->only('email', 'password'),
            $request->has('remember')
        );

        return $loggedIn
            ? redirect($request->input('referer', '/'))
            : back()->withInput()->withErrors(__('Invalid credentials.'));
    }

    public function logout()
    {
        Auth::logout();

        return redirect(request()->get('redirect', '/'));
    }

    public function register()
    {
        $registrar = new UserRegistrar(request());

        $validator = $registrar->validator();

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $user = $registrar->create();

        // Allow addons to prevent the submission of the form, return
        // their own errors, and modify the user.
        $errors = $this->runRegisteringEvent($user);

        if (! $errors->isEmpty()) {
            return back()->withInput()->withErrors($errors);
        }

        $user->save();

        event('user.registered', $user);

        Auth::login($user);

        $redirect = request()->input('redirect', '/');

        return redirect($redirect);
    }

    /**
     * Run the `user.registering` event.
     *
     * This allows the registration to be short-circuited before it gets saved and show errors.
     * Or, the user may be modified. Lastly, an addon could just 'do something' here without
     * modifying/stopping the registration.
     *
     * Expects an array of event responses (multiple listeners can listen for the same event).
     * Each response in the array should be another array with an `errors` array.
     *
     * @param  UserContract $user
     * @return MessageBag
     */
    protected function runRegisteringEvent(UserContract $user)
    {
        $errors = [];

        $responses = event('user.registering', $user);

        foreach ($responses as $response) {
            // Ignore any non-arrays
            if (! is_array($response)) {
                continue;
            }

            // If the event returned errors, tack those onto the array.
            if ($response_errors = array_get($response, 'errors')) {
                $errors = array_merge($response_errors, $errors);
                continue;
            }
        }

        return new MessageBag($errors);
    }
}
