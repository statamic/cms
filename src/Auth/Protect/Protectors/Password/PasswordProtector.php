<?php

namespace Statamic\Auth\Protect\Protectors\Password;

use Facades\Statamic\Auth\Protect\Protectors\Password\Token;
use Statamic\Auth\Protect\Protectors\Protector;
use Statamic\Exceptions\ForbiddenHttpException;

class PasswordProtector extends Protector
{
    /**
     * Provide protection.
     *
     * @return void
     */
    public function protect()
    {
        if (empty(array_get($this->config, 'allowed', []))) {
            throw new ForbiddenHttpException();
        }

        if (request()->isLivePreview()) {
            return;
        }

        if ($this->isPasswordFormUrl()) {
            return;
        }

        if (! $this->hasEnteredValidPassword()) {
            $this->redirectToPasswordForm();
        }
    }

    public function hasEnteredValidPassword()
    {
        return (new Guard($this->scheme))->check(
            session("statamic:protect:password.passwords.{$this->scheme}")
        );
    }

    protected function isPasswordFormUrl()
    {
        return $this->url === $this->getPasswordFormUrl();
    }

    protected function redirectToPasswordForm()
    {
        $url = $this->getPasswordFormUrl().'?token='.$this->generateToken();

        abort(redirect($url));
    }

    protected function getPasswordFormUrl()
    {
        return url($this->config['form_url'] ?? route('statamic.protect.password.show'));
    }

    protected function generateToken()
    {
        $token = Token::generate();

        session()->put("statamic:protect:password.tokens.$token", [
            'scheme' => $this->scheme,
            'url' => $this->url,
        ]);

        return $token;
    }
}
