<?php

namespace Statamic\Auth\Protect\Protectors\Password;

use Facades\Statamic\Auth\Protect\Protectors\Password\Token;
use Statamic\Auth\Protect\Protectors\Protector;
use Statamic\Exceptions\ForbiddenHttpException;
use Statamic\Support\Arr;

class PasswordProtector extends Protector
{
    /**
     * Provide protection.
     *
     * @return void
     */
    public function protect()
    {
        if (empty($this->schemePasswords()) && ! $this->localPasswords()) {
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

    protected function schemePasswords()
    {
        return Arr::get($this->config, 'allowed', []);
    }

    public function localPasswords()
    {
        if (! $field = Arr::get($this->config, 'field')) {
            return [];
        }

        return Arr::wrap($this->data->$field);
    }

    public function hasEnteredValidPassword()
    {
        if (
            ($password = session("statamic:protect:password.passwords.scheme.{$this->scheme}"))
            && $this->isValidSchemePassword($password)
        ) {
            return true;
        }

        if (
            $this->data
            && ($password = session("statamic:protect:password.passwords.ref.{$this->data->reference()}"))
            && $this->isValidLocalPassword($password)
        ) {
            return true;
        }

        return false;
    }

    public function isValidPassword(string $password): bool
    {
        return $this->isValidSchemePassword($password) || $this->isValidLocalPassword($password);
    }

    public function isValidSchemePassword(string $password): bool
    {
        return in_array($password, $this->schemePasswords());
    }

    public function isValidLocalPassword(string $password): bool
    {
        return in_array($password, $this->localPasswords());
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
            'reference' => $this->data?->reference(),
        ]);

        return $token;
    }
}
