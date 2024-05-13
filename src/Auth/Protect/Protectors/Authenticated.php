<?php

namespace Statamic\Auth\Protect\Protectors;

use Statamic\Exceptions\ForbiddenHttpException;
use Statamic\Support\Arr;

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
            throw new ForbiddenHttpException();
        }

        abort(redirect($this->getLoginUrl()));
    }

    protected function getLoginUrl()
    {
        if (! $url = Arr::get($this->config, 'login_url')) {
            return null;
        }

        if (! $this->shouldAppendRedirect()) {
            return $url;
        }

        $url = parse_url($url);

        if ($query = Arr::get($url, 'query')) {
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
        return Arr::get($this->config, 'append_redirect', false);
    }
}
