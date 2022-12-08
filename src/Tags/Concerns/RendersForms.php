<?php

namespace Statamic\Tags\Concerns;

use Closure;
use Illuminate\Support\MessageBag;

trait RendersForms
{
    use RendersAttributes;

    protected function formAttrs($action, $method = 'POST', $knownTagParams = [], $additionalAttrs = [])
    {
        $formMethod = $method === 'GET' ? 'GET' : 'POST';

        $attrs = array_merge([
            'method' => $formMethod,
            'action' => $action,
        ], $additionalAttrs);

        if ($this->params->bool('files')) {
            $attrs['enctype'] = 'multipart/form-data';
        }

        $paramAttrs = collect($this->params->all())
            ->except(array_merge(['method', 'action'], $knownTagParams))
            ->mapWithKeys(function ($value, $attribute) {
                return [preg_replace('/^attr:/', '', $attribute) => $value];
            })
            ->all();

        return array_merge($attrs, $paramAttrs);
    }

    protected function formParams($method, $params = [])
    {
        if ($this->params->bool('csrf', true)) {
            $params['token'] = csrf_token();
        }

        $method = strtoupper($method);

        if (! in_array($method, ['GET', 'POST'])) {
            $params['method'] = $method;
        }

        return $params;
    }

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

        if ($this->params->bool('csrf', true)) {
            $html .= csrf_field();
        }

        $method = strtoupper($method);

        if (! in_array($method, ['GET', 'POST'])) {
            $html .= method_field($method);
        }

        return $html;
    }

    protected function formMetaPrefix($meta)
    {
        return collect($meta)
            ->mapWithKeys(function ($value, $key) {
                return ['_'.$key => $value];
            })
            ->all();
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
     * @param  bool|Closure  $manipulateDataCallback
     * @return array
     */
    protected function getRenderableField($field, $errorBag = 'default', $manipulateDataCallback = false)
    {
        $errors = session('errors') ? session('errors')->getBag($errorBag) : new MessageBag;

        $missing = str_random();
        $old = old($field->handle(), $missing);
        $default = $field->value() ?? $field->defaultValue();
        $value = $old === $missing ? $default : $old;

        $data = array_merge($field->toArray(), [
            'error' => $errors->first($field->handle()) ?: null,
            'default' => $field->value() ?? $field->defaultValue(),
            'old' => old($field->handle()),
            'value' => $value,
        ]);

        if ($manipulateDataCallback instanceof Closure) {
            $data = $manipulateDataCallback($data, $field);
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
        // Leave whitespace around these html elements.
        $ignoredHtmlElements = collect(['a', 'span'])->implode('|');

        // Trim whitespace between all other html elements.
        $html = preg_replace('/\s*(<(?!\/*('.$ignoredHtmlElements.'))[^>]+>)\s*/', '$1', $html);

        return $html;
    }
}
