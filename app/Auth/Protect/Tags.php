<?php

namespace Statamic\Auth\Protect;

use Illuminate\Support\ViewErrorBag;
use Statamic\Extend\Tags as BaseTags;

class Tags extends BaseTags
{
    public function passwordForm()
    {
        if (! $token = request('token')) {
            return $this->parse([
                'errors' => [],
                'no_token' => true
            ]);
        }

        $html = $this->formOpen(route('protect.password'));

        $html .= '<input type="hidden" name="token" value="'.$token.'" />';

        $errors = session('errors', new ViewErrorBag)->passwordProtect;

        $html .= $this->parse([
            'errors' => $errors->toArray(),
            'error' => $errors->first()
        ]);

        $html .= '</form>';

        return $html;
    }
}
