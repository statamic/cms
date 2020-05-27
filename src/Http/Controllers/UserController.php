<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;

class UserController extends Controller
{
    private $request;

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $loggedIn = Auth::attempt(
            $request->only('email', 'password'),
            $request->has('remember')
        );

        return $loggedIn
            ? redirect($request->input('_redirect', '/'))->withSuccess(__('Login successful.'))
            : back()->withInput()->withErrors(__('Invalid credentials.'));
    }

    public function logout()
    {
        Auth::logout();

        return redirect(request()->get('redirect', '/'));
    }

    public function register(Request $request)
    {
        $blueprint = Blueprint::find('user');

        $fields = $blueprint->fields()->addValues($request->all());

        $fieldRules = $fields->validator()->withRules([
            'email' => 'required|email|unique_user_value',
            'password' => 'required|confirmed',
        ])->rules();

        $request->validateWithBag('user.register', $fieldRules);

        $values = $fields->process()->values()->except(['email', 'groups', 'roles']);

        $user = User::make()
            ->email($request->email)
            ->password($request->password)
            ->data($values);

        if ($roles = config('statamic.users.new_user_roles')) {
            $user->roles($roles);
        }

        // TODO: Registering event

        $user->save();

        // TODO: Registered event

        Auth::login($user);

        $response = $request->has('_redirect') ? redirect($request->get('_redirect')) : back();

        session()->flash('user.register.success', __('Registration successful.'));

        return $response;
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
