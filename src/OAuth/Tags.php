<?php

namespace Statamic\OAuth;

use Statamic\Facades\OAuth;
use Statamic\Tags\Tags as BaseTags;

class Tags extends BaseTags
{
    public static $handle = 'oauth';

    /**
     * Shorthand for generating an OAuth login URL.
     *
     * Maps to {{ oauth:[provider] }}
     */
    public function wildcard($tag)
    {
        return $this->generateLoginUrl($tag);
    }

    /**
     * Generate a login URL.
     *
     * Maps to {{ oauth:login_url }}
     *
     * @return string
     */
    public function loginUrl()
    {
        return $this->generateLoginUrl($this->params->get(['provider', 'for']));
    }

    /**
     * Generate the login URL.
     *
     * @param  string  $provider
     * @return string
     */
    protected function generateLoginUrl($provider)
    {
        $url = OAuth::provider($provider)->loginUrl();

        if ($redirect = $this->params->get('redirect')) {
            $url .= "?redirect=$redirect";
        }

        return $url;
    }
}
