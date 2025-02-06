<?php

namespace Statamic\Tags\Concerns;

use Closure;
use Illuminate\Support\MessageBag;
use Statamic\Fields\Field;
use Statamic\Forms\RenderableField;
use Statamic\Support\Str;

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

        $attrs = $this->renderAttributesFromParamsWith(
            $attrs,
            except: array_merge(['method', 'action'], $knownTagParams)
        );

        $html = collect(['<form', $attrs])->filter()->implode(' ').'>';

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

        $missing = Str::random();
        $old = old($field->handle(), $missing);
        $default = $field->value() ?? $field->defaultValue();
        $value = $old === $missing ? $default : $old;

        $configDefaults = Field::commonFieldOptions()->all()
            ->merge($field->fieldtype()->configFields()->all())
            ->map->get('default')
            ->filter()->all();

        $formHandle = $field->form()?->handle() ?? Str::slug($errorBag);

        $data = array_merge($configDefaults, $field->toArray(), [
            'handle' => $field->handle(),
            'name' => $this->convertDottedHandleToInputName($field->handle()),
            'id' => $this->generateFieldId($field->handle(), $formHandle),
            'instructions' => $field->instructions(),
            'error' => $errors->first($field->handle()) ?: null,
            'default' => $field->value() ?? $field->defaultValue(),
            'old' => old($field->handle()), // TODO: Ensure dotted path for old input works here.
            'value' => $value,
        ], $field->fieldtype()->extraRenderableFieldData());

        if ($field->fieldtype()->handle() === 'group') {
            $data['fields'] = collect($field->fieldtype()->fields()->all())
                ->map(fn ($child) => $child->setHandle($field->handle().'.'.$child->handle()))
                ->map(fn ($child) => $this->getRenderableField($child, $errorBag, $manipulateDataCallback))
                ->values()
                ->all();
        }

        if ($manipulateDataCallback instanceof Closure) {
            $data = $manipulateDataCallback($data, $field);
        }

        $data['field'] = new RenderableField($field, $data);

        return $data;
    }

    /**
     * Generate a field id to associate input with label.
     */
    private function generateFieldId(string $fieldHandle, ?string $formName = null): string
    {
        return ($formName ?? 'default').'-form-'.$fieldHandle.'-field';
    }

    /**
     * Convert dotted handle to input name that can be submitted as array value in form html.
     */
    protected function convertDottedHandleToInputName(string $handle): string
    {
        $parts = collect(explode('.', $handle));

        $first = $parts->pull(0);

        return $first.$parts
            ->map(fn ($part) => '['.$part.']')
            ->join('');
    }
}
