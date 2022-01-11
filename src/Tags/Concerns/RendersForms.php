<?php

namespace Statamic\Tags\Concerns;

use Illuminate\Support\MessageBag;
use Statamic\Forms\JsDrivers\JsDriver;
use Statamic\Support\Html;

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
     * @param  string  $errorBag
     * @param  JsDriver  $jsDriver
     * @return array
     */
    protected function getRenderableField($field, $errorBag = 'default', $jsDriver = false)
    {
        $errors = session('errors') ? session('errors')->getBag($errorBag) : new MessageBag;

        $data = array_merge($field->toArray(), [
            'error' => $errors->first($field->handle()) ?: null,
            'old' => old($field->handle()),
        ]);

        if ($jsDriver) {
            $data['js_driver'] = $jsDriver->handle();
            $data['js_attributes'] = $this->renderAttributes($jsDriver->addToRenderableFieldAttributes($field));
            $data = array_merge($data, $jsDriver->addToRenderableFieldData($data, $field));
        }

        $data['field'] = $this->minifyFieldHtml(view($field->fieldtype()->view(), $data)->render());

        return $data;
    }

    /**
     * Minify field html.
     *
     * @param  string  $html
     * @return string
     */
    protected function minifyFieldHtml($html)
    {
        // Trim whitespace between elements.
        $html = preg_replace('/>\s*([^<>]*)\s*</', '>$1<', $html);

        return $html;
    }
}
