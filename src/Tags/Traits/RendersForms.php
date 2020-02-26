<?php

namespace Statamic\Tags\Traits;

use Statamic\Extend\HasParameters;

trait RendersForms
{
    use HasParameters;

    /**
     * Open a form.
     *
     * @param string $action
     * @return string
     */
    protected function formOpen($action, $method = 'POST')
    {
        $formMethod = $method === 'GET' ? 'GET' : 'POST';

        $defaultAttrs = [
            "method:{$formMethod}",
            "action:{$action}",
        ];

        if ($this->getBool('files')) {
            $defaultAttrs[] = 'enctype:multipart/form-data';
        }

        $attrs = collect($defaultAttrs)
            ->merge($this->getList('attr'))
            ->mapWithKeys(function ($attr) {
                $bits = preg_split('/:(?!\/{2})/', $attr);
                return [$bits[0] => $bits[1] ?? null];
            })
            ->map(function ($value, $attr) {
                return $value ? "{$attr}=\"{$value}\"" : $attr;
            })
            ->implode(' ');

        $html = "<form {$attrs}>";
        $html .= csrf_field();

        $method = strtoupper($method);

        if (! in_array($method, ['GET', 'POST'])) {
            $html .= method_field($method);
        }

        return $html;
    }

    /**
     * Close a form.
     *
     * @return string
     */
    protected function formClose()
    {
        return '</form>';
    }
}
