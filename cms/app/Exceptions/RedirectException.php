<?php

namespace Statamic\Exceptions;

/**
 * Trigger a redirect
 */
class RedirectException extends \Exception
{
    protected $url;

    protected $code = 302;

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
     * @deprecated Use getCode
     */
    public function getStatusCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @deprecated Use setCode
     */
    public function setStatusCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }
}
