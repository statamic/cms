<?php

namespace Statamic\OAuth;

use Statamic\Facades\OAuth;
use Statamic\Facades\User;
use Statamic\Tags\Concerns;
use Statamic\Tags\Tags as BaseTags;

class Tags extends BaseTags
{
    use Concerns\RendersForms;

    public static $handle = 'oauth';

    /**
     * Loop over the available providers.
     *
     * Maps to {{ oauth }} ... {{ /oauth }}
     */
    public function index()
    {
        $user = User::current();

        $providers = OAuth::providers()
            ->reject(fn (Provider $provider) => $provider->isStateless())
            ->map(fn (Provider $provider) => [
                'name' => $provider->name(),
                'label' => $provider->label(),
                'connected' => $user ? $provider->isConnectedTo($user) : false,
                'url' => $this->generateLoginUrl($provider->name()),
            ])
            ->values();

        if (! $this->canParseContents()) {
            return $providers->all();
        }

        return $providers->isEmpty()
            ? $this->parseNoResults()
            : $this->parseLoop($providers->all());
    }

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
     * Output a form to disconnect a provider from the current user.
     *
     * Maps to {{ oauth:disconnect_form }}
     *
     * @return string
     */
    public function disconnectForm()
    {
        $provider = $this->params->get(['provider', 'for']);

        if (! $provider || ! User::current()) {
            return '';
        }

        $action = route('statamic.oauth.disconnect', $provider);
        $method = 'POST';

        $knownParams = ['provider', 'for'];

        if (! $this->canParseContents()) {
            return [
                'attrs' => $this->formAttrs($action, $method, $knownParams),
                'params' => array_merge(
                    $this->formMetaPrefix($this->formParams($method, [])),
                    ['_method' => 'DELETE']
                ),
            ];
        }

        $html = $this->formOpen($action, $method, $knownParams);

        $html .= '<input type="hidden" name="_method" value="DELETE" />';

        $html .= $this->parse([]);

        $html .= $this->formClose();

        return $html;
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
