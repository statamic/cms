<?php

namespace Statamic\Auth\Protect;

use Statamic\Tags\Tags as BaseTags;
use Illuminate\Support\ViewErrorBag;

class Tags extends BaseTags
{
    protected static $handle = 'protect';

    public function passwordForm()
    {
        if (! $token = request('token')) {
            return $this->parse([
                'errors' => [],
                'no_token' => true
            ]);
        }

        $html = $this->formOpen(route('statamic.protect.password.store'));

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
