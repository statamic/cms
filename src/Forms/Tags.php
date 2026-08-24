<?php

namespace Statamic\Forms;

use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DebugBarException;
use Illuminate\Support\Collection;
use Illuminate\Support\Uri;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Antlers;
use Statamic\Facades\Blink;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Statamic\Fields\Tab;
use Statamic\Forms\JsDrivers\AbstractJsDriver;
use Statamic\Forms\JsDrivers\JsDriver;
use Statamic\Forms\Logic\PageLogic;
use Statamic\Support\Arr;
use Statamic\Support\Html;
use Statamic\Support\Str;
use Statamic\Tags\Concerns;
use Statamic\Tags\Tags as BaseTags;
use Statamic\View\Antlers\Language\Runtime\GlobalRuntimeState;

use function Statamic\trans as __;

class Tags extends BaseTags
{
    use Concerns\GetsFormSession,
        Concerns\GetsQueryResults,
        Concerns\GetsRedirects,
        Concerns\OutputsItems,
        Concerns\QueriesConditions,
        Concerns\QueriesOrderBys,
        Concerns\QueriesScopes,
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

        return $this->parse();
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

        $data['form_config'] = ($configFields = Form::extraConfigFor($form->handle()))
            ? Blueprint::makeFromTabs($configFields)->fields()->addValues($form->data()->all())->augment()->values()->all()
            : [];

        $data['pages'] = $this->getPages($this->sessionHandle(), $jsDriver);

        $data['page'] = Arr::except(collect($data['pages'])->firstWhere('id', Arr::get($this->currentPage(), 'id')), 'sections');

        $data['sections'] = $this->getSections($this->sessionHandle(), $jsDriver);

        $data['fields'] = collect($data['sections'])->flatMap->fields->all();

        $data['honeypot'] = $form->honeypot();

        $data['button_label'] = Arr::get($data['page'], 'button_label');

        if (! $this->isFirstPage()) {
            $data['previous_page_label'] = Arr::get($data['page'], 'previous_page_label');
            $data['previous_page_url'] = $this->previousPageUrl();
        }

        $instance = $form->instance($this->context->value('id'));

        $data['restricted'] = $instance->restricted();
        $data['restriction_message'] = $instance->restrictionMessage();
        $data['status'] = $instance->status();

        if ($jsDriver) {
            $data['js_driver'] = $jsDriver->handle();
            $data['show_field'] = $jsDriver->copyShowFieldToFormData($data['fields']);
            $data = array_merge($data, $jsDriver->addToFormData($data));
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

        $attrs = $this->runHooks('attrs', ['attrs' => $attrs, 'data' => $data])['attrs'];

        $params = [];

        if ($redirect = $this->getRedirectUrl()) {
            $params['redirect'] = $this->parseRedirect($redirect);
        }

        if ($errorRedirect = $this->getErrorRedirectUrl()) {
            $params['error_redirect'] = $this->parseRedirect($errorRedirect);
        }

        if ($form->hasMultiplePages()) {
            $params['page'] = Arr::get($this->currentPage(), 'id');
        }

        if ($entry = $instance->entry()) {
            $params['entry'] = $entry;
        }

        if (! $this->canParseContents()) {
            return array_merge([
                'attrs' => $this->formAttrs($action, $method, $knownParams, $attrs),
                'params' => $this->formMetaPrefix($this->formParams($method, $params)),
            ], $data);
        }

        $html = $this->formOpen($action, $method, $knownParams, $attrs);
        $html = $this->runHooks('after-open', ['html' => $html, 'data' => $data])['html'];

        $html .= $this->formMetaFields($params);

        $html .= $this->parse($data);

        $html = $this->runHooks('before-close', ['html' => $html, 'data' => $data])['html'];
        $html .= $this->formClose();

        if ($jsDriver) {
            return $jsDriver->render($html);
        }

        return $html;
    }

    /**
     * Maps to {{ form:fields }}.
     *
     * @return string
     */
    public function fields()
    {
        $isBlade = $this->isAntlersBladeComponent();

        $scope = $this->params->get('scope');

        $slot = new RenderableFieldSlot(
            html: $this->content,
            scope: $scope,
            isBlade: $isBlade,
        );

        collect($this->context['fields'])
            ->each(fn ($field) => $field['field']->slot($slot));

        $context = $this->context->all();

        $fields = Arr::get($context, 'fields', []);

        if ($handle = $this->params->get('get')) {
            $context['fields'] = $this->dottedContextFields($fields, recursive: true)->only($handle)->values()->all();
        } elseif ($only = $this->params->get('only')) {
            $context['fields'] = $this->dottedContextFields($fields)->only(explode('|', $only))->values()->all();
        } elseif ($except = $this->params->get('except')) {
            $context['fields'] = $this->dottedContextFields($fields)->except(explode('|', $except))->values()->all();
        }

        if ($isBlade) {
            return $this->tagRenderer->render('@foreach($fields as $field)'.$this->content.'@endforeach', $context);
        }

        $params = '';

        if ($scope) {
            $params = Html::attributes(['scope' => $scope]);
        }

        return Antlers::parse('{{ fields '.$params.' }}'.$this->content.'{{ /fields }}', $context, ! GlobalRuntimeState::$isEvaluatingUserData);
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
        $successMessage = $this->getFromFormSession($sessionHandle, 'success');

        if ($this->isAntlersBladeComponent() && $this->isPair) {
            return str($successMessage)->length() > 0;
        }

        return $successMessage;
    }

    /**
     * Maps to {{ form:submission }}.
     *
     * @return array|void
     */
    public function submission()
    {
        if ($this->success()) {
            return $this->aliasedResult(session('submission')->toArray());
        }
    }

    /**
     * Maps to {{ form:submissions }}.
     *
     * @return array
     */
    public function submissions()
    {
        $query = $this->form()->querySubmissions();

        $this->queryConditions($query);
        $this->queryScopes($query);
        $this->queryOrderBys($query);

        return $this->output($this->results($query));
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
     * Get sections of fields for the current page, using sections defined in blueprint.
     *
     * @param  string  $sessionHandle
     * @param  JsDriver  $jsDriver
     * @return array
     */
    protected function getSections($sessionHandle, $jsDriver)
    {
        return $this->form()->blueprint()->tabs()
            ->filter(fn ($tab) => $tab->handle() === Arr::get($this->currentPage(), 'id'))
            ->flatMap(fn ($tab) => $this->getSectionsForTab($tab, $sessionHandle, $jsDriver))
            ->values()
            ->all();
    }

    /**
     * Get the sections of fields for a single page (blueprint tab).
     *
     * @param  \Statamic\Fields\Tab  $tab
     * @param  string  $sessionHandle
     * @param  JsDriver  $jsDriver
     * @return \Illuminate\Support\Collection
     */
    private function getSectionsForTab($tab, $sessionHandle, $jsDriver)
    {
        return $tab->sections()
            ->map(function ($section) use ($sessionHandle, $jsDriver) {
                $fields = $section->fields();

                if ($partialSubmission = $this->getPartialSubmission()) {
                    $fields = $fields->addValues($partialSubmission->data()->all());
                }

                return [
                    'display' => $section->display(),
                    'instructions' => $section->instructions(),
                    'fields' => $this->getFields($sessionHandle, $jsDriver, $fields->all()),
                ];
            });
    }

    /**
     * Get pages of sections, using the tabs defined in the blueprint.
     *
     * @param  string  $sessionHandle
     * @param  JsDriver  $jsDriver
     * @return array
     */
    protected function getPages($sessionHandle, $jsDriver)
    {
        $tabs = $this->form()->blueprint()->tabs()->values();

        return $tabs
            ->map(function (Tab $tab, $index) use ($tabs, $sessionHandle, $jsDriver) {
                $contents = $tab->contents();
                $isFirstPage = $index === 0;
                $isLastPage = $index === $tabs->count() - 1;

                $page = [
                    'id' => $tab->handle(),
                    'display' => $tab->display(),
                    'instructions' => $tab->instructions(),
                    'button_label' => $contents['button_label'] ?? ($isLastPage ? __('Submit') : __('Next')),
                    'sections' => $this->getSectionsForTab($tab, $sessionHandle, $jsDriver)->all(),
                ];

                if (! $isFirstPage) {
                    $showPreviousButton = $contents['show_previous_button'] ?? filled($contents['previous_page_label'] ?? null);

                    if ($showPreviousButton) {
                        $page['previous_page_label'] = filled($contents['previous_page_label'] ?? null)
                            ? $contents['previous_page_label']
                            : __('Previous Page');
                    }
                }

                if ($jsDriver instanceof AbstractJsDriver) {
                    $page = array_merge($page, $jsDriver->addToRenderablePageData($tab, $page));
                }

                return $page;
            })
            ->all();
    }

    /**
     * Get fields with extra data for looping over and rendering.
     *
     * @param  string  $sessionHandle
     * @param  JsDriver  $jsDriver
     * @param  array|null  $fields
     * @return array
     */
    protected function getFields($sessionHandle, $jsDriver, $fields = null)
    {
        $form = $this->form();

        return collect($fields ?? $form->fields())
            ->each(fn ($field) => $field->setForm($form)->setFormField($form->formFields()->field($field->handle())))
            ->map(function ($field) use ($sessionHandle, $jsDriver) {
                return $this->getRenderableField($field, $sessionHandle, function ($data, $field) use ($jsDriver) {
                    $data['is_informative'] = in_array('information', $field->formField()?->fieldtype()?->categories() ?? []);

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

        $instance = new $class($form, $options, $this->params);

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
        if (! function_exists('debugbar') || ! debugbar()->isEnabled() || ! class_exists(ConfigCollector::class)) {
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

    private function isFirstPage(): bool
    {
        $pages = $this->form()->formFields()->pages();

        return Arr::get($pages->first(), 'id') === Arr::get($this->currentPage(), 'id');
    }

    private function previousPage(): ?string
    {
        $pages = $this->form()->formFields()->pages();
        $currentPageId = Arr::get($this->currentPage(), 'id');
        $firstPageId = Arr::get($pages->first(), 'id');

        if ($currentPageId === $firstPageId) {
            return null;
        }

        $data = $this->getPartialSubmission()?->data()->all() ?? [];
        $path = (new PageLogic($this->form()))->pathTo($data, $currentPageId);
        $currentIndex = array_search($currentPageId, $path, true);

        // The current page comes from the ?page= query param, so it may point to a
        // page the logic wouldn't actually route to and won't be on the path.
        if ($currentIndex === false) {
            return $firstPageId;
        }

        return $path[$currentIndex - 1] ?? null;
    }

    private function previousPageUrl(): ?string
    {
        if (! $previousPage = $this->previousPage()) {
            return null;
        }

        return Uri::of(url()->current())->withQuery(['page' => $previousPage])->__toString();
    }

    private function currentPage(): array
    {
        $pages = $this->form()->formFields()->pages();
        $page = $pages->first();

        if (request()->has('page') && $pages->where('id', request()->get('page'))->count() > 0) {
            $page = $pages->where('id', request()->get('page'))->first();
        }

        return $page;
    }

    private function getPartialSubmission(): ?Submission
    {
        $id = session()->get("form.{$this->form()->handle()}.partial_submission");
        $submission = $this->form()->submission($id);

        if ($submission && ! $submission->isPartial()) {
            return null;
        }

        return $submission;
    }

    private function dottedContextFields(array $fields, $recursive = false, array &$dotted = []): Collection
    {
        foreach ($fields as $field) {
            $dotted[$field['handle']] = $field;

            if ($recursive && $fields = Arr::get($field, 'fields')) {
                $this->dottedContextFields($fields, $recursive, $dotted);
            }
        }

        return collect($dotted);
    }
}
