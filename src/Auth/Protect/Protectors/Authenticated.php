<?php

namespace Statamic\Auth\Protect\Protectors;

class Authenticated extends Protector
{
    public function protect()
    {
        if (auth()->check()) {
            return;
        }

        if ($this->isLoginUrl()) {
            return;
        }

        if (! $this->getLoginUrl()) {
            abort(403);
        }

        abort(redirect($this->getLoginUrl()));
    }

    protected function getLoginUrl()
    {
        if (! $url = array_get($this->config, 'login_url')) {
            return null;
        }

        if (! $this->shouldAppendRedirect()) {
            return $url;
        }

        $url = parse_url($url);

        if ($query = array_get($url, 'query')) {
            $query .= '&';
        }

        return $url['path'].'?'.$query.'redirect='.$this->url;
    }

    protected function isLoginUrl()
    {
        return parse_url($this->url, PHP_URL_PATH) === parse_url((string) $this->getLoginUrl(), PHP_URL_PATH);
    }

    protected function shouldAppendRedirect()
    {
        return array_get($this->config, 'append_redirect', false);
    }
}
