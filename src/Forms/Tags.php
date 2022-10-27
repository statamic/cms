<?php

namespace Statamic\Forms;

use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DebugBarException;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Facades\Blink;
use Statamic\Facades\Form;
use Statamic\Facades\URL;
use Statamic\Forms\JsDrivers\JsDriver;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Tags\Concerns;
use Statamic\Tags\Tags as BaseTags;

class Tags extends BaseTags
{
    use Concerns\GetsFormSession,
        Concerns\GetsRedirects,
        Concerns\OutputsItems,
        Concerns\RendersForms;

    const HANDLE_PARAM = ['handle', 'is', 'in', 'form', 'formset'];

    protected static $handle = 'form';

    /**
     * {{ form:* }} ... {{ /form:* }}.
     */
    public function __call($method, $args)
    {
        $this->params['form'] = $this->method;

        return $this->create();
    }

    /**
     * Maps to {{ form:set }}.
     *
     * Allows you to inject the formset into the context so child tags can use it.
     *
     * @return array
     */
    public function set()
    {
        $this->context['form'] = $this->params->get(static::HANDLE_PARAM);

        return [];
    }

    /**
     * Maps to {{ form:create }}.
     *
     * @return string
     */
    public function create()
    {
        $formHandle = $this->getForm();
        $form = $this->form();

        $data = $this->getFormSession($this->sessionHandle());

        $jsDriver = $this->parseJsParamDriverAndOptions($this->params->get('js'), $form);

        $data['fields'] = $this->getFields($this->sessionHandle(), $jsDriver);
        $data['honeypot'] = $form->honeypot();

        if ($jsDriver) {
            $data['js_driver'] = $jsDriver->handle();
            $data['show_field'] = $jsDriver->copyShowFieldToFormData($data['fields']);
            $data = array_merge($data, $jsDriver->addToFormData($form, $data));
        }

        $this->addToDebugBar($data, $formHandle);

        if (! $this->params->has('files')) {
            $this->params->put('files', $form->hasFiles());
        }

        $knownParams = array_merge(static::HANDLE_PARAM, [
            'redirect', 'error_redirect', 'allow_request_redirect', 'csrf', 'files', 'js',
        ]);

        $action = $this->params->get('action', $form->actionUrl());
        $method = $this->params->get('method', 'POST');

        $attrs = [];

        if ($jsDriver) {
            $attrs = array_merge($attrs, $jsDriver->addToFormAttributes($form));
        }

        $params = [];

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        if (! $this->parser) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method, $knownParams, $attrs),
                'params' => $this->formMetaPrefix($this->formParams($method, $params)),
            ], $data);
        }

        $html = $this->formOpen($action, $method, $knownParams, $attrs);

        $html .= $this->formMetaFields($params);

        $html .= $this->parse($data);

        $html .= $this->formClose();

        if ($jsDriver) {
            return $jsDriver->render($html);
        }

        return $html;
    }

    /**
     * Maps to {{ form:errors }}.
     *
     * @return bool|string
     */
    public function errors()
    {
        $sessionHandle = $this->sessionHandle();

        $errors = $this->getFormSession($sessionHandle)['errors'];

        // If this is a single tag just output a boolean.
        if ($this->content === '') {
            return ! empty($errors);
        }

        return $this->parseLoop(collect($errors)->map(function ($error) {
            return ['value' => $error];
        }));
    }

    /**
     * Maps to {{ form:success }}.
     *
     * @return bool
     */
    public function success()
    {
        $sessionHandle = $this->sessionHandle();

        // TODO: Should probably output success string instead of `true` boolean for consistency.
        return $this->getFromFormSession($sessionHandle, 'success');
    }

    /**
     * Maps to {{ form:submission }}.
     *
     * @return array|void
     */
    public function submission()
    {
        if ($this->success()) {
            return session('submission')->toArray();
        }
    }

    /**
     * Maps to {{ form:submissions }}.
     *
     * @return array
     */
    public function submissions()
    {
        $submissions = $this->form()->submissions();

        return $this->output($submissions);
    }

    /**
     * Get the sort order for a collection.
     *
     * @return string
     */
    protected function getSortOrder()
    {
        return $this->params->get('sort', 'date');
    }

    /**
     * Get the formset specified either by the parameter or from within the context.
     *
     * @return string
     */
    protected function getForm()
    {
        if (! $handle = $this->formHandle()) {
            throw new \Exception('A form handle is required on Form tags. Please refer to the docs for more information.');
        }

        if (! $this->form()) {
            throw new \Exception("Form with handle [$handle] cannot be found.");
        }

        return $handle;
    }

    /**
     * Get fields with extra data for looping over and rendering.
     *
     * @param  string  $sessionHandle
     * @param  JsDriver  $jsDriver
     * @return array
     */
    protected function getFields($sessionHandle, $jsDriver)
    {
        return $this->form()->fields()
            ->map(function ($field) use ($sessionHandle, $jsDriver) {
                return $this->getRenderableField($field, $sessionHandle, function ($data, $field) use ($jsDriver) {
                    return $jsDriver
                        ? $this->mergeJsDataWithRenderableFieldData($data, $field, $jsDriver)
                        : $data;
                });
            })
            ->values()
            ->all();
    }

    /**
     * Merge JS field data with renderable field data.
     *
     * @param  array  $data
     * @param  \Statamic\Fields\Field  $field
     * @param  JsDriver  $jsDriver
     * @return array
     */
    protected function mergeJsDataWithRenderableFieldData($data, $field, $jsDriver)
    {
        $data['js_driver'] = $jsDriver->handle();
        $data['js_attributes'] = $this->renderAttributes($jsDriver->addToRenderableFieldAttributes($field));

        return array_merge($data, $jsDriver->addToRenderableFieldData($field, $data));
    }

    /**
     * Parse JS param to get driver and related options.
     *
     * @param  null|string  $value
     * @param  \Statamic\Forms\Form  $form
     * @return bool|JsDriver
     */
    protected function parseJsParamDriverAndOptions($value, $form)
    {
        if (! $value) {
            return false;
        }

        $handle = $value;
        $options = [];

        if (Str::contains($value, ':')) {
            $options = explode(':', $value);
            $handle = array_shift($options);
        }

        $class = app('statamic.form-js-drivers')->get($handle);

        if (! $class) {
            throw new \Exception("Cannot find JS driver class for [{$handle}]!");
        }

        $instance = new $class($form, $options);

        if (! $instance instanceof JsDriver) {
            throw new \Exception("JS driver must implement [Statamic\Forms\JsDrivers\JsDriver] interface!");
        }

        return $instance;
    }

    /**
     * Add data to the debug bar.
     *
     * Each form on the page will have its data placed in an array named
     * by its name. We'll use blink to keep track of the data as
     * we go and just update the collector.
     *
     * @param  array  $data
     */
    protected function addToDebugBar($data, $formHandle)
    {
        if (! function_exists('debugbar') || ! class_exists(ConfigCollector::class)) {
            return;
        }

        $blink = Blink::store();

        $debug = array_merge([$formHandle => $data], $blink->get('debug_bar_data', []));

        $blink->put('debug_bar_data', $debug);

        try {
            debugbar()->getCollector('Forms')->setData($debug);
        } catch (DebugBarException $e) {
            // Collector doesn't exist yet. We'll create it.
            debugbar()->addCollector(new ConfigCollector($debug, 'Forms'));
        }
    }

    protected function sessionHandle()
    {
        return 'form.'.$this->getForm();
    }

    protected function form()
    {
        $handle = $this->formHandle();

        return Blink::once("form-$handle", function () use ($handle) {
            return Form::find($handle);
        });
    }

    protected function formHandle()
    {
        $form = $this->params->get(static::HANDLE_PARAM, Arr::get($this->context, 'form'));

        if ($form instanceof FormContract) {
            $handle = $form->handle();
            Blink::put("form-$handle", $form);
            $form = $handle;
        }

        return $form;
    }

    public function eventUrl($url, $relative = true)
    {
        return URL::prependSiteUrl(
            config('statamic.routes.action').'/form/'.$url
        );
    }
}
