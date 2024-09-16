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

    protected function localPasswords()
    {
        if (! $field = Arr::get($this->config, 'field')) {
            return [];
        }

        return Arr::wrap($this->data->$field);
    }

    protected function validPasswords()
    {
        return collect($this->schemePasswords())
            ->merge($this->localPasswords())
            ->filter()
            ->unique()
            ->all();
    }

    public function hasEnteredValidPassword()
    {
        $schemePassed = (new Guard($this->validPasswords()))->check(
            session("statamic:protect:password.passwords.scheme.{$this->scheme}")
        );

        $localPassed = (new Guard($this->validPasswords()))->check(
            session("statamic:protect:password.passwords.ref.{$this->data->reference()}")
        );

        return $schemePassed || $localPassed;
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
            'reference' => $this->data->reference(),
            'valid_passwords' => $this->validPasswords(),
            'local_passwords' => $this->localPasswords(),
        ]);

        return $token;
    }
}
