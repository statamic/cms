<?php

namespace Statamic\Addons\OAuth;

use Statamic\API\OAuth;
use Statamic\Extend\Tags;

class OAuthTags extends Tags
{
    /**
     * Shorthand for generating an OAuth login URL
     *
     * Maps to {{ oauth:[provider] }}
     *
     * @param string $method
     * @param array $args
     * @return string
     */
    public function __call($method, $args)
    {
        return $this->generateLoginUrl($method);
    }

    /**
     * Generate a login URL
     *
     * Maps to {{ oauth:login_url }}
     *
     * @return string
     */
    public function loginUrl()
    {
        return $this->generateLoginUrl($this->get(['provider', 'for']));
    }

    /**
     * Generate the login URL
     *
     * @param string $provider
     * @return string
     */
    protected function generateLoginUrl($provider)
    {
        $url = OAuth::route($provider);

        if ($redirect = $this->get('redirect')) {
            $url .= "?redirect=$redirect";
        }

        return $url;
    }
}
