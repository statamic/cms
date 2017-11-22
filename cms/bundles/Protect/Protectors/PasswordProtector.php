<?php

namespace Statamic\Addons\Protect\Protectors;

use Statamic\API\Str;
use Statamic\Exceptions\RedirectException;

class PasswordProtector extends AbstractProtector
{
    /**
     * Whether or not this provides protection.
     *
     * @return bool
     */
    public function providesProtection()
    {
        return ! empty(array_get($this->scheme, 'allowed', []));
    }

    /**
     * Provide protection
     *
     * @return void
     */
    public function protect()
    {
        if ($this->isPasswordFormUrl()) {
            return;
        }

        if (! $this->hasPassword()) {
            $this->redirectToPasswordForm();
        }
    }

    public function isValidPassword($password)
    {
        return in_array($password, $this->getAllowedPasswords());
    }

    public function hasPassword()
    {
        return ! empty(array_intersect($this->getUserPasswords(), $this->getAllowedPasswords()));
    }

    protected function getUserPasswords()
    {
        $key = $this->siteWide ? 'site' : md5($this->url);

        return session()->get("protect.password.passwords.{$key}", []);
    }

    protected function isPasswordFormUrl()
    {
        return $this->url === $this->getPasswordFormUrl();
    }

    protected function getAllowedPasswords()
    {
        return array_get($this->scheme, 'allowed', []);
    }

    protected function redirectToPasswordForm()
    {
        $e = new RedirectException;

        $e->setUrl($this->getRedirectUrl());

        throw $e;
    }

    protected function getPasswordFormUrl()
    {
        $default = '/'; // @todo

        return array_get($this->scheme, 'form_url', $default);
    }

    protected function getRedirectUrl()
    {
        return $this->getPasswordFormUrl() . '?token=' . $this->generateToken();
    }

    protected function generateToken()
    {
        $token = Str::random(32);

        session()->put("protect.password.scheme.$token", [
            'scheme' => $this->scheme,
            'url' => $this->url,
            'siteWide' => $this->siteWide
        ]);

        return $token;
    }
}
