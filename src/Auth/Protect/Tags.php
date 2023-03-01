<?php

namespace Statamic\Auth\Protect;

use Illuminate\Support\ViewErrorBag;
use Statamic\Support\Html;
use Statamic\Tags\Concerns;
use Statamic\Tags\Tags as BaseTags;

class Tags extends BaseTags
{
    use Concerns\RendersForms;

    protected static $handle = 'protect';

    public function passwordForm()
    {
        if (! $token = Html::entities(request('token'))) {
            $data = [
                'errors' => [],
                'no_token' => true,
            ];

            return $this->parser ? $this->parse($data) : $data;
        }

        $errors = session('errors', new ViewErrorBag)->passwordProtect;

        $data = [
            'errors' => $errors->toArray(),
            'error' => $errors->first(),
        ];

        $action = route('statamic.protect.password.store');
        $method = 'POST';

        if (! $this->parser) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method),
                'params' => array_merge($this->formMetaPrefix($this->formParams($method)), [
                    'token' => $token,
                ]),
            ], $data);
        }

        $html = $this->formOpen($action, $method);

        $html .= '<input type="hidden" name="token" value="'.$token.'" />';

        $html .= $this->parse($data);

        $html .= $this->formClose();

        return $html;
    }
}
