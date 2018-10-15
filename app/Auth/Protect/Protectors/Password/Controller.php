<?php

namespace Statamic\Auth\Protect\Protectors\Password;

use Statamic\Auth\Protect\Protectors\Password\Guard;
use Statamic\Auth\Protect\Protectors\PasswordProtector;
use Statamic\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{
    protected $tokenData;

    public function password()
    {
        $this->password = request('password');
        $this->tokenData = session('statamic:protect:password.tokens.' . request('token'));

        if (! $this->tokenData) {
            return back()->withErrors(['token' => 'Invalid or expired token.'], 'passwordProtect');
        }

        $guard = new Guard($this->getScheme());

        if (! $guard->check($this->password)) {
            return back()->withErrors(['password' => 'Incorrect password.'], 'passwordProtect');
        }

        return $this
            ->storePassword()
            ->expireToken()
            ->redirect();
    }

    protected function getScheme()
    {
        return $this->tokenData['scheme'];
    }

    protected function getUrl()
    {
        return $this->tokenData['url'];
    }

    protected function storePassword()
    {
        session()->put(
            "statamic:protect:password.passwords.{$this->getScheme()}",
            $this->password
        );

        return $this;
    }

    protected function expireToken()
    {
        $token = request('token');

        session()->forget("statamic:protect:password.tokens.$token");

        return $this;
    }

    protected function redirect()
    {
        return redirect($this->tokenData['url']);
    }
}
