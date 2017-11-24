<?php

namespace Statamic\Addons\Protect\Protectors;

use Statamic\Exceptions\RedirectException;

class LoggedInProtector extends AbstractProtector
{
    /**
     * Whether or not this provides protection.
     *
     * @return bool
     */
    public function providesProtection()
    {
        return true;
    }

    /**
     * Provide protection
     *
     * @return void
     */
    public function protect()
    {
        if (auth()->check()) {
            return;
        }

        if ($this->isLoginUrl()) {
            return;
        }

        if (! $this->getLoginUrl()) {
            $this->deny();
        }

        throw tap(new RedirectException, function ($e) {
            $e->setUrl($this->getLoginUrl());
        });
    }

    protected function getLoginUrl()
    {
        if (! $url = array_get($this->scheme, 'login_url')) {
            return null;
        }

        if (! $this->shouldAppendRedirect()) {
            return $url;
        }

        $url = parse_url($url);

        if ($query = array_get($url, 'query')) {
            $query .= '&';
        }

        return $url['path'] . '?' . $query . 'redirect=' . $this->url;
    }

    protected function isLoginUrl()
    {
        return parse_url($this->url, PHP_URL_PATH) === parse_url($this->getLoginUrl(), PHP_URL_PATH);
    }

    protected function shouldAppendRedirect()
    {
        return array_get($this->scheme, 'append_redirect', false);
    }
}
