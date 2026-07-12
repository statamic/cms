<?php

namespace Statamic\Tags\Concerns;

use Statamic\Support\Str;

trait GetsRedirects
{
    /**
     * Get the redirect URL.
     *
     * @return string
     */
    protected function getRedirectUrl()
    {
        $return = $this->params->get('redirect');

        if ($this->params->get('allow_request_redirect')) {
            $return = request()->input('redirect', $return);
        }

        return $return;
    }

    /**
     * Get the error redirect URL.
     *
     * @return string
     */
    protected function getErrorRedirectUrl()
    {
        $return = $this->params->get('error_redirect');

        if ($this->params->get('allow_request_redirect')) {
            $return = request()->input('error_redirect', $return);
        }

        return $return;
    }

    /**
     * Parse redirect URL.
     *
     * @param  string  $redirect
     * @return string
     */
    protected function parseRedirect($redirect)
    {
        if (Str::startsWith($redirect, '#')) {
            return request()->url().$redirect;
        }

        return $redirect;
    }
}
