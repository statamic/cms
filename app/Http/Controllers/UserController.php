<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Auth;
use Statamic\API\User;
use Statamic\API\Request;
use Statamic\Auth\PasswordReset;
use Statamic\Auth\UserRegistrar;
use Illuminate\Support\MessageBag;
use Statamic\Contracts\Auth\User as UserContract;

class UserController extends Controller
{
    private $request;

    public function login()
    {
        $validator = \Validator::make(Request::all(), [
            'username' => 'required',
            'password' => 'required'
        ], [], [
            'username' => 'username field',
            'password' => 'password field',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $logged_in = Auth::login(
            Request::input('username'),
            Request::input('password'),
            Request::has('remember')
        );

        if (! $logged_in) {
            return back()->withInput()->withErrors('Invalid credentials.');
        }

        $redirect = Request::input('redirect', '/');

        return redirect($redirect);
    }

    public function logout()
    {
        \Auth::logout();

        return redirect(Request::get('redirect', '/'));
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

        $redirect = Request::input('redirect', '/');

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
