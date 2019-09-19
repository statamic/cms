<?php

namespace Statamic\Exceptions;

/**
 * Trigger a redirect
 */
class RedirectException extends \Exception
{
    protected $url;

    protected $code = 302;

    public function render()
    {
        return redirect($this->getUrl(), $this->getCode());
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }
}
