<?php

namespace Statamic\Tags\Concerns;

use Illuminate\Support\MessageBag;
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
     * @param  bool|string  $alpine
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

        if ($alpine) {
            $data['alpine_data_key'] = $this->getAlpineXDataKey($data['handle'], $alpine);
            $data['alpine_show_field'] = $this->renderAlpineShowFieldJs($field->conditions(), $alpine);
        }

        $data['field'] = $this->minifyFieldHtml(view($field->fieldtype()->view(), $data)->render());

        return $data;
    }

    /**
     * Render alpine x-data string for field handles, with scope if necessary.
     *
     * @param  array|\Illuminate\Support\Collection  $fieldHandles
     * @param  bool|string  $alpineScope
     * @return string
     */
    protected function renderAlpineXData($fieldHandles, $alpineScope)
    {
        $xData = collect($fieldHandles)
            ->mapWithKeys(function ($fieldHandle) {
                return [$fieldHandle => old($fieldHandle)];
            })
            ->all();

        if (is_string($alpineScope)) {
            $xData = [
                $alpineScope => $xData,
            ];
        }

        return $this->jsonEncodeForHtmlAttribute($xData);
    }

    /**
     * Get alpine x-data key, with scope if necessary.
     *
     * @param  string  $fieldHandle
     * @param  bool|string  $alpineScope
     * @return string
     */
    protected function getAlpineXDataKey($fieldHandle, $alpineScope)
    {
        return is_string($alpineScope)
            ? "{$alpineScope}.{$fieldHandle}"
            : $fieldHandle;
    }

    /**
     * Render alpine `x-if` show field JS logic.
     *
     * @param  array  $conditions
     * @param  string  $alpineScope
     * @return string
     */
    protected function renderAlpineShowFieldJs($conditions, $alpineScope)
    {
        if (is_string($alpineScope)) {
            $conditions = collect($conditions)->map(function ($fields) use ($alpineScope) {
                return collect($fields)->mapWithKeys(function ($condition, $fieldHandle) use ($alpineScope) {
                    return ["{$alpineScope}.{$fieldHandle}" => $condition];
                });
            });
        }

        $attrFriendlyConditions = $this->jsonEncodeForHtmlAttribute($conditions);

        return 'Statamic.$conditions.showField('.$attrFriendlyConditions.', $data)';
    }

    /**
     * Json encode for html attribute.
     *
     * @param  mixed  $value
     * @return string
     */
    protected function jsonEncodeForHtmlAttribute($value)
    {
        return str_replace('"', '\'', json_encode($value));
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
