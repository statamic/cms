<?php

namespace Statamic\Addons\Protect;

use Statamic\API\Request;
use Statamic\Extend\Tags;

class ProtectTags extends Tags
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
            'error' => $this->flash->get('error')
        ]);

        $html .= '</form>';

        return $html;
    }
}
