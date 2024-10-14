<?php

namespace Statamic\Auth\Protect\Protectors\Password;

use Statamic\Auth\Protect\ProtectorManager;
use Statamic\Facades\Data;
use Statamic\Facades\Site;
use Statamic\Http\Controllers\Controller as BaseController;
use Statamic\View\View;

class Controller extends BaseController
{
    protected $tokenData;
    protected $password;

    public function show()
    {
        if ($this->tokenData = session('statamic:protect:password.tokens.'.request('token'))) {
            $site = Site::findByUrl($this->getUrl());
            $data = Data::find($this->tokenData['reference']);

            app()->setLocale($site->lang());
        }

        return View::make('statamic::auth.protect.password')->cascadeContent($data ?? null);
    }

    public function store()
    {
        $this->password = request('password');
        $this->tokenData = session('statamic:protect:password.tokens.'.request('token'));

        if (! $this->tokenData) {
            return back()->withErrors(['token' => __('statamic::messages.password_protect_token_invalid')], 'passwordProtect');
        }

        if (is_null($this->password) || ! $this->driver()->isValidPassword($this->password)) {
            return back()->withErrors(['password' => __('statamic::messages.password_protect_incorrect_password')], 'passwordProtect');
        }

        return $this
            ->storePassword()
            ->expireToken()
            ->redirect();
    }

    private function driver(): PasswordProtector
    {
        return app(ProtectorManager::class)
            ->driver($this->getScheme())
            ->setData(Data::find($this->getReference()));
    }

    protected function getScheme()
    {
        return $this->tokenData['scheme'];
    }

    protected function getUrl()
    {
        return $this->tokenData['url'];
    }

    protected function getReference()
    {
        return $this->tokenData['reference'];
    }

    protected function storePassword()
    {
        $sessionKey = $this->driver()->isValidLocalPassword($this->password)
            ? "statamic:protect:password.passwords.ref.{$this->getReference()}"
            : "statamic:protect:password.passwords.scheme.{$this->getScheme()}";

        session()->put($sessionKey, $this->password);

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
