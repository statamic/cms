<?php

namespace Statamic\Auth\Protect;

use Statamic\API\URL;
use Statamic\API\Request;
use Statamic\Extend\Tags as BaseTags;

class Tags extends BaseTags
{
    /**
     * Password form
     *
     * @return string
     */
    public function passwordForm()
    {
        if (! $token = Request::get('token')) {
            return $this->parse(['no_token' => true]);
        }

        $html = $this->formOpen('password');

        $html .= '<input type="hidden" name="token" value="'.$token.'" />';

        $html .= $this->parse([
            'error' => session()->get('error')
        ]);

        $html .= '</form>';

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function eventUrl($url, $relative = false)
    {
        return URL::prependSiteUrl(
            config('statamic.routes.action') . '/protect/' . $url
        );
    }
}
