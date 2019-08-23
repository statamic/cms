<?php

namespace Statamic\Http\Controllers\CP\Auth;

use Statamic\API\Str;
use Statamic\API\OAuth;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Statamic\Http\Middleware\CP\RedirectIfAuthorized;

class LoginController extends CpController
{
    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(RedirectIfAuthorized::class)->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm(Request $request)
    {
        $data = [
            'title' => __('Login'),
            'oauth' => $enabled = OAuth::enabled(),
            'providers' => $enabled ? OAuth::providers() : [],
            'referer' => url()->previous()
        ];

        $view = view('statamic::auth.login', $data);

        if ($request->expired) {
            return $view->withErrors(t('session_expired'));
        }

        return $view;
    }

    public function redirectPath()
    {
        $referer = request('referer');

        return Str::contains($referer, '/'.config('statamic.cp.route')) ? $referer : cp_route('index');
    }

    protected function authenticated(Request $request, $user)
    {
        return $request->expectsJson()
            ? response('Authenticated')
            : redirect()->intended($this->redirectPath());
    }

    protected function credentials(Request $request)
    {
        $credentials = [
            $this->username() => strtolower($request->get($this->username())),
            'password' => $request->get('password')
        ];

        return $credentials;
    }

    protected function loggedOut(Request $request)
    {
        return redirect($request->redirect ?? '/');
    }
}
