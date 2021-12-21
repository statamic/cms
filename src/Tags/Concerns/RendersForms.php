<?php

namespace Statamic\Tags\Concerns;

use Illuminate\Support\MessageBag;

trait RendersForms
{
    use RendersAttributes;

    /**
     * Open a form.
     *
     * @param  string  $action
     * @param  string  $method
     * @param  array  $knownTagParams
     * @param  array  $additionalAttrs
     * @return string
     */
    protected function formOpen($action, $method = 'POST', $knownTagParams = [], $additionalAttrs = [])
    {
        $formMethod = $method === 'GET' ? 'GET' : 'POST';

        $attrs = array_merge([
            'method' => $formMethod,
            'action' => $action,
        ], $additionalAttrs);

        if ($this->params->bool('files')) {
            $attrs['enctype'] = 'multipart/form-data';
        }

        $attrs = $this->renderAttributes($attrs);
        $paramAttrs = $this->renderAttributesFromParams(array_merge(['method', 'action'], $knownTagParams));

        $html = collect(['<form', $attrs, $paramAttrs])->filter()->implode(' ').'>';
        $html .= csrf_field();

        $method = strtoupper($method);

        if (! in_array($method, ['GET', 'POST'])) {
            $html .= method_field($method);
        }

        return $html;
    }

    protected function formMetaFields($meta)
    {
        return collect($meta)
            ->map(function ($value, $key) {
                return sprintf('<input type="hidden" name="_%s" value="%s" />', $key, $value);
            })
            ->implode("\n");
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

    /**
     * Get field with extra data for rendering.
     *
     * @param  \Statamic\Fields\Field  $field
     * @param  bool  $alpine
     * @return array
     */
    protected function getRenderableField($field, $errorBag = 'default', $alpine = false)
    {
        $errors = session('errors') ? session('errors')->getBag($errorBag) : new MessageBag;

        $data = array_merge($field->toArray(), [
            'error' => $errors->first($field->handle()) ?: null,
            'old' => old($field->handle()),
            'alpine' => $alpine,
        ]);

        $data['field'] = view($field->fieldtype()->view(), $data);

        return $data;
    }
}
