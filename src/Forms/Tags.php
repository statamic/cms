<?php

namespace Statamic\Forms;

use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DebugBarException;
use Statamic\Facades\Blink;
use Statamic\Facades\Form;
use Statamic\Facades\URL;
use Statamic\Support\Arr;
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
        $data['fields'] = $this->getFields($this->sessionHandle());
        $data['honeypot'] = $form->honeypot();

        $this->addToDebugBar($data, $formHandle);

        if (! $this->params->has('files')) {
            $this->params->put('files', $form->hasFiles());
        }

        $knownParams = array_merge(static::HANDLE_PARAM, ['redirect', 'error_redirect', 'allow_request_redirect', 'files']);

        $action = $this->params->get('action', route('statamic.forms.submit', $formHandle));
        $method = $this->params->get('method', 'POST');

        $html = $this->formOpen($action, $method, $knownParams);

        $params = [];

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        $html .= $this->formMetaFields($params);

        $html .= $this->parse($data);

        $html .= $this->formClose();

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
     * @return array
     */
    protected function getFields($sessionHandle)
    {
        return $this->form()->fields()
            ->map(function ($field) use ($sessionHandle) {
                return $this->getRenderableField($field, $sessionHandle);
            })
            ->values()
            ->all();
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
        return $this->params->get(static::HANDLE_PARAM, Arr::get($this->context, 'form'));
    }

    public function eventUrl($url, $relative = true)
    {
        return URL::prependSiteUrl(
            config('statamic.routes.action').'/form/'.$url
        );
    }
}
