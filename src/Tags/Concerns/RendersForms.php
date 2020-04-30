<?php

namespace Statamic\Tags\Concerns;

use Statamic\Extend\HasParameters;

trait RendersForms
{
    use HasParameters, RendersAttributes;

    /**
     * Open a form.
     *
     * @param string $action
     * @return string
     */
    protected function formOpen($action, $method = 'POST', $knownTagParams = [])
    {
        $formMethod = $method === 'GET' ? 'GET' : 'POST';

        $defaultAttrs = [
            'method' => $formMethod,
            'action' => $action,
        ];

        if ($this->getBool('files')) {
            $defaultAttrs['enctype'] = 'multipart/form-data';
        }

        $defaultAttrs = $this->renderAttributes($defaultAttrs);
        $additionalAttrs = $this->renderAttributesFromParams(array_merge(['method', 'action'], $knownTagParams));

        $html = collect(['<form', $defaultAttrs, $additionalAttrs])->filter()->implode(' ').'>';
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
