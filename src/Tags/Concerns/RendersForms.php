<?php

namespace Statamic\Tags\Concerns;

use Illuminate\Support\MessageBag;

trait RendersForms
{
    use RendersAttributes;

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

        if ($this->params->bool('files')) {
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
     * @param \Statamic\Fields\Field $field
     * @return array
     */
    protected function getRenderableField($field, $errorBag = 'default')
    {
        $errors = session('errors') ? session('errors')->getBag($errorBag) : new MessageBag;

        $data = array_merge($field->toArray(), [
            'error' => $errors->first($field->handle()) ?: null,
            'old' => old($field->handle()),
        ]);

        $data['field'] = view($field->fieldtype()->view(), $data);

        return $data;
    }
}
