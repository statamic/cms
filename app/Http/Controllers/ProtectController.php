<?php

namespace Statamic\Http\Controllers;

use Statamic\Auth\Protect\Protectors\PasswordProtector;

class ProtectController extends Controller
{
    /**
     * @var PasswordProtector
     */
    protected $protector;

    public function password()
    {
        if (! $token = $this->getTokenData()) {
            session()->flash('error', 'Invalid or expired token.');
            return back();
        }

        $this->protector = new PasswordProtector($this->getUrl(), $this->getScheme());

        if (! $this->protector->isValidPassword($this->getPassword())) {
            session()->flash('error', 'Incorrect password.');
            return back();
        }

        $this->storePassword();

        return redirect($this->getUrl());
    }

    protected function getScheme()
    {
        return array_get($this->getTokenData(), 'scheme');
    }

    protected function isSiteWide()
    {
        return array_get($this->getTokenData(), 'siteWide');
    }

    protected function getUrl()
    {
        return array_get($this->getTokenData(), 'url');
    }

    protected function getPassword()
    {
        return request('password');
    }

    protected function getToken()
    {
        return request('token');
    }

    protected function getTokenData()
    {
        return session()->get('protect.password.scheme.'.$this->getToken());
    }

    protected function storePassword()
    {
        $passwords = session()->get('protect.password.passwords', []);

        $key = $this->isSiteWide() ? 'site' : md5($this->getUrl());

        $passwords[$key][] = $this->getPassword();

        session()->put('protect.password.passwords', $passwords);
    }
}
