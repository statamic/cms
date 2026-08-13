<?php

namespace Statamic\Forms;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\Contracts\Forms\Submission;
use Statamic\Contracts\Forms\SubmissionQueryBuilder;
use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Data\ContainsData;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Events\FormBlueprintFound;
use Statamic\Events\FormCreated;
use Statamic\Events\FormCreating;
use Statamic\Events\FormDeleted;
use Statamic\Events\FormDeleting;
use Statamic\Events\FormSaved;
use Statamic\Events\FormSaving;
use Statamic\Facades;
use Statamic\Facades\Blink;
use Statamic\Facades\File;
use Statamic\Facades\Form as FormFacade;
use Statamic\Facades\FormSubmission;
use Statamic\Facades\User;
use Statamic\Facades\YAML;
use Statamic\Fields\Blueprint;
use Statamic\Forms\Exporters\Exporter;
use Statamic\Forms\Fields\FormFields;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

use function Statamic\trans as __;

class Form implements Arrayable, Augmentable, ContainsQueryableValues, FormContract
{
    use ContainsData, FluentlyGetsAndSets, HasAugmentedInstance;

    protected $handle;
    protected $title;
    protected $fields;
    protected $honeypot;
    protected $store;
    protected $connections;
    protected $afterSaveCallbacks = [];
    protected $withEvents = true;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function __clone()
    {
        $this->data = clone $this->data;
        $this->supplements = clone $this->supplements;
    }

    /**
     * Get or set the handle.
     *
     * @param  mixed  $handle
     * @return mixed
     */
    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    /**
     * Get or set the title.
     *
     * @param  mixed  $title
     * @return mixed
     */
    public function title($title = null)
    {
        return $this
            ->fluentlyGetOrSet('title')
            ->getter(function ($title) {
                return $title ?? ucfirst($this->handle);
            })
            ->args(func_get_args());
    }

    public function formFields($fields = null)
    {
        return $this
            ->fluentlyGetOrSet('fields')
            ->getter(function ($fields) {
                if (empty($fields) && $blueprint = Facades\Blueprint::find("forms.{$this->handle()}")) {
                    $fields = $this->convertFieldsFromBlueprint($blueprint);
                }

                return new FormFields($fields ?? []);
            })
            ->setter(function ($fields) {
                Blink::forget('form-blueprint-'.$this->handle());

                if (isset($fields['tabs'])) {
                    $fields = [
                        'sections' => collect($fields['tabs'])->flatMap(fn ($tab) => $tab['sections'])->all(),
                    ];
                }

                if (isset($fields['fields'])) {
                    $fields = [
                        'sections' => [
                            ['fields' => $fields['fields']],
                        ],
                    ];
                }

                return $fields;
            })
            ->args(func_get_args());
    }

    private function convertFieldsFromBlueprint(Blueprint $blueprint): array
    {
        $sections = collect($blueprint->contents()['tabs'] ?? [])->flatMap(function (array $tab): array {
            return collect($tab['sections'] ?? [])->map(function (array $section): array {
                return [
                    ...$section,
                    'fields' => collect($section['fields'] ?? [])->map(function (array $field): array {
                        $validate = Arr::get($field, 'field.validate');
                        $validateRules = is_string($validate) ? explode('|', $validate) : ($validate ?? []);

                        $isEmailRule = fn ($rule) => is_string($rule) && ($rule === 'email' || str_starts_with($rule, 'email:'));

                        if (Arr::get($field, 'field.type') === 'text' && collect($validateRules)->contains($isEmailRule)) {
                            Arr::set($field, 'field.type', 'email');
                            Arr::pull($field, 'field.input_type');

                            $remainingValidationRules = collect($validateRules)
                                ->reject($isEmailRule)
                                ->values();

                            if ($remainingValidationRules->isEmpty()) {
                                unset($field['field']['validate']);
                            } else {
                                $field['field']['validate'] = $remainingValidationRules->all();
                            }
                        }

                        $isUrlRule = fn ($rule) => is_string($rule) && ($rule === 'url' || str_starts_with($rule, 'url:'));

                        if (Arr::get($field, 'field.type') === 'text' && collect($validateRules)->contains($isUrlRule)) {
                            Arr::set($field, 'field.type', 'website');
                            Arr::pull($field, 'field.input_type');

                            $remainingValidationRules = collect($validateRules)
                                ->reject($isUrlRule)
                                ->values();

                            if ($remainingValidationRules->isEmpty()) {
                                unset($field['field']['validate']);
                            } else {
                                $field['field']['validate'] = $remainingValidationRules->all();
                            }
                        }

                        if (Arr::get($field, 'field.type') === 'text' && Arr::get($field, 'field.input_type') === 'tel') {
                            Arr::set($field, 'field.type', 'phone');
                            Arr::pull($field, 'field.input_type');
                        }

                        if (
                            Arr::get($field, 'field.type') === 'text'
                            && (! Arr::has($field, 'field.input_type') || Arr::get($field, 'field.input_type') === 'text')
                        ) {
                            Arr::set($field, 'field.type', 'short_answer');
                            Arr::pull($field, 'field.input_type');
                        }

                        if (Arr::get($field, 'field.type') === 'textarea') {
                            Arr::set($field, 'field.type', 'long_answer');
                        }

                        if (Arr::get($field, 'field.type') === 'time') {
                            Arr::set($field, 'field.type', 'time_picker');
                        }

                        if (Arr::get($field, 'field.type') === 'integer') {
                            Arr::set($field, 'field.type', 'number');
                        }

                        if (Arr::get($field, 'field.type') === 'radio') {
                            Arr::set($field, 'field.type', 'multi_choice');
                        }

                        if (Arr::get($field, 'field.type') === 'select') {
                            Arr::set($field, 'field.type', 'dropdown');

                            if (Arr::get($field, 'field.multiple') === true) {
                                if ($maxItems = Arr::pull($field, 'field.max_items')) {
                                    Arr::set($field, 'field.max_selections', $maxItems);
                                }
                            } else {
                                Arr::pull($field, 'field.multiple');
                                Arr::pull($field, 'field.max_items');
                            }
                        }

                        if (Arr::get($field, 'field.type') === 'assets') {
                            Arr::set($field, 'field.type', 'upload');
                            Arr::set($field, 'field.store', true);
                        }

                        if (Arr::get($field, 'field.type') === 'files') {
                            Arr::set($field, 'field.type', 'upload');
                            Arr::set($field, 'field.store', false);
                        }

                        return $field;
                    })->all(),
                ];
            })->all();
        })->all();

        return ['sections' => $sections];
    }

    public function hasMultiplePages(): bool
    {
        if (! Statamic::formsProInstalled()) {
            return false;
        }

        return $this->formFields()->pages()->count() > 1;
    }

    /**
     * Get the blueprint.
     *
     * @return mixed
     */
    public function blueprint()
    {
        if (Blink::has($blink = 'form-blueprint-'.$this->handle())) {
            return Blink::get($blink);
        }

        $blueprint = $this->formFields()->toBlueprint();

        Blink::put($blink, $blueprint);

        FormBlueprintFound::dispatch($blueprint, $this);

        return $blueprint;
    }

    /**
     * Get or set the honeypot field.
     *
     * @param  mixed  $honeypot
     * @return mixed
     */
    public function honeypot($honeypot = null)
    {
        return $this->fluentlyGetOrSet('honeypot')
            ->getter(function ($honeypot) {
                return $honeypot ?? 'honeypot';
            })
            ->setter(function ($honeypot) {
                return $honeypot === 'honeypot' ? null : $honeypot;
            })
            ->args(func_get_args());
    }

    /**
     * Get or set the store field.
     *
     * @param  mixed  $store
     * @return mixed
     */
    public function store($store = null)
    {
        return $this->fluentlyGetOrSet('store')
            ->getter(function ($store) {
                return $store !== false;
            })
            ->setter(function ($store) {
                return $store === false ? false : null;
            })
            ->args(func_get_args());
    }

    /**
     * Get or set the connection configs.
     *
     * @param  mixed  $connections
     * @return mixed
     */
    public function connections($connections = null)
    {
        return $this->fluentlyGetOrSet('connections')
            ->getter(fn ($connections) => collect($connections))
            ->setter(fn ($connections) => collect($connections))
            ->args(func_get_args());
    }

    /**
     * Get or set the email field.
     *
     * @deprecated Use connections() instead.
     *
     * @param  mixed  $emails
     * @return mixed
     */
    public function email($emails = null)
    {
        if (func_num_args() === 0) {
            return $this->connections()->get('email');
        }

        $connections = $this->connections();

        is_null($emails)
            ? $connections->forget('email')
            : $connections->put('email', $this->convertEmailToConnection($emails));

        return $this->connections($connections);
    }

    private function convertEmailToConnection(array $emails): array
    {
        return collect(array_is_list($emails) ? $emails : [$emails])
            ->map(fn ($config) => ['id' => Str::random(8), ...$config])
            ->all();
    }

    /**
     * Get the form fields off the blueprint.
     *
     * @return \Illuminate\Support\Collection
     */
    public function fields()
    {
        return $this->blueprint()->fields()->all();
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function path()
    {
        return config('statamic.forms.forms')."/{$this->handle()}.yaml";
    }

    public function afterSave($callback)
    {
        $this->afterSaveCallbacks[] = $callback;

        return $this;
    }

    public function saveQuietly()
    {
        $this->withEvents = false;

        return $this->save();
    }

    /**
     * Save form.
     */
    public function save()
    {
        $isNew = is_null(FormFacade::find($this->handle()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if ($isNew && FormCreating::dispatch($this) === false) {
                return false;
            }

            if (FormSaving::dispatch($this) === false) {
                return false;
            }
        }

        $data = $this->data->merge(collect([
            'title' => $this->title,
            'fields' => $this->formFields()->contents(),
            'honeypot' => $this->honeypot,
            'connections' => $this->connectionsFileData(),
        ]))->filter()->all();

        if ($this->store === false) {
            $data['store'] = false;
        }

        if ($this->get('generate_fake_submissions') === false) {
            $data['generate_fake_submissions'] = false;
        }

        File::put($this->path(), YAML::dump($data));

        if ($blueprint = Facades\Blueprint::find("forms.{$this->handle()}")) {
            $blueprint->delete();
        }

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        if ($withEvents) {
            if ($isNew) {
                FormCreated::dispatch($this);
            }

            FormSaved::dispatch($this);
        }
    }

    private function connectionsFileData(): array
    {
        return $this->connections()
            ->map(function ($config) {
                if (! is_array($config)) {
                    return $config;
                }

                return array_is_list($config)
                    ? array_map(fn ($item) => is_array($item) ? Arr::removeNullValues($item) : $item, $config)
                    : Arr::removeNullValues($config);
            })
            ->all();
    }

    public function deleteQuietly()
    {
        $this->withEvents = false;

        return $this->delete();
    }

    /**
     * Delete form and associated submissions.
     */
    public function delete()
    {
        $withEvents = $this->withEvents;
        $this->withEvents = true;

        if ($withEvents && FormDeleting::dispatch($this) === false) {
            return false;
        }

        $this->submissions()->each->delete();

        File::delete($this->path());

        if ($withEvents) {
            FormDeleted::dispatch($this);
        }

        return true;
    }

    /**
     * Hydrate form from file data.
     *
     * @return $this
     */
    public function hydrate()
    {
        $contents = YAML::parse(File::get($this->path()));

        $methods = [
            'title',
            'honeypot',
            'store',
            'connections',
        ];

        $this->merge(collect($contents)->except([...$methods, 'email', 'fields']));

        collect($contents)
            ->filter(function ($value, $property) use ($methods) {
                return in_array($property, $methods);
            })
            ->each(function ($value, $property) {
                $this->{$property}($value);
            });

        if (! is_null($emails = Arr::get($contents, 'connections.email', $contents['email'] ?? null))) {
            $this->connections($this->connections()->put('email', $this->convertEmailToConnection($emails)));
        }

        if (isset($contents['fields'])) {
            $this->formFields($contents['fields']);
        }

        return $this;
    }

    /**
     * Get the submissions.
     *
     * @return \Illuminate\Support\Collection
     */
    public function submissions()
    {
        return FormSubmission::whereForm($this->handle());
    }

    public function querySubmissions(): SubmissionQueryBuilder
    {
        return FormSubmission::query()->where('form', $this->handle());
    }

    public function status(): string
    {
        return Blink::once('form-status-'.$this->handle(), fn () => match (true) {
            $this->closingDateHasPassed() => 'closed',
            $this->submissionLimitReached() => 'limit_reached',
            default => 'open',
        });
    }

    public function restricted(): bool
    {
        return $this->restrictionMessage() !== null;
    }

    public function restrictionMessage(): ?string
    {
        if ($this->closingDateHasPassed() || $this->submissionLimitReached()) {
            return ($msg = $this->get('closed_message')) ? __($msg) : __('statamic::messages.form_closed_message');
        }

        if ($this->get('require_login') && ! User::current()) {
            return ($msg = $this->get('require_login_message')) ? __($msg) : __('statamic::messages.form_require_login_message');
        }

        return null;
    }

    private function closingDateHasPassed(): bool
    {
        if (! $date = $this->get('close_date')) {
            return false;
        }

        return Carbon::parse($date, config('app.timezone'))->isPast();
    }

    private function submissionLimitReached(): bool
    {
        if (! $limit = (int) $this->get('submission_limit')) {
            return false;
        }

        return $this->submissionCount() >= $limit;
    }

    private function submissionCount(): int
    {
        $query = $this->querySubmissions()->whereNull('partial');

        if ($start = $this->submissionLimitPeriodStart()) {
            $query->where('date', '>=', $start);
        }

        return $query->count();
    }

    private function submissionLimitPeriodStart(): ?Carbon
    {
        return match ($this->get('submission_limit_period', 'total')) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => null,
        };
    }

    /**
     * Get a submission.
     *
     * @param  string  $id
     * @return Submission
     */
    public function submission($id)
    {
        return FormSubmission::find($id);
    }

    /**
     * Make a form submission.
     *
     * @return Submission
     */
    public function makeSubmission()
    {
        $submission = FormSubmission::make();

        $submission->form($this);

        return $submission;
    }

    /**
     * The URL to view the form in the CP.
     *
     * @return string
     */
    public function showUrl()
    {
        return cp_route('forms.show', $this->handle());
    }

    /**
     * The URL to view form submissions in the CP.
     *
     * @return string
     */
    public function submissionsUrl()
    {
        return cp_route('forms.submissions.index', $this->handle());
    }

    /**
     * The URL to edit this in the CP.
     *
     * @return string
     */
    public function editUrl()
    {
        return cp_route('forms.edit', $this->handle());
    }

    /**
     * The URL to delete this in the CP.
     *
     * @return string
     */
    public function deleteUrl()
    {
        return cp_route('forms.destroy', $this->handle());
    }

    /** @deprecated */
    public function editBlueprintUrl()
    {
        return cp_route('forms.show', $this->handle());
    }

    public function hasFiles()
    {
        return $this->fields()->filter(function ($field) {
            return in_array($field->fieldtype()->handle(), ['assets', 'files', 'form_upload']);
        })->isNotEmpty();
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedForm($this);
    }

    public function shallowAugmentedArrayKeys()
    {
        return ['handle', 'title', 'api_url'];
    }

    public function apiUrl()
    {
        return Statamic::apiRoute('forms.show', $this->handle());
    }

    /**
     * Get the form action url.
     *
     * @return string
     */
    public function actionUrl()
    {
        return route('statamic.forms.submit', $this->handle());
    }

    public function exporters()
    {
        return FormFacade::exporters()
            ->all()
            ->filter->allowedOnForm($this)
            ->each->setForm($this);
    }

    public function exporter(string $handle): ?Exporter
    {
        return $this->exporters()->get($handle);
    }

    public function getQueryableValue(string $field)
    {
        if (in_array($method = Str::camel($field), $this->queryableMethods())) {
            return $this->{$method}();
        }

        $value = $this->get($field);

        if (! $field = $this->blueprint()->field($field)) {
            return $value;
        }

        return $field->fieldtype()->toQueryableValue($value);
    }

    private function queryableMethods(): array
    {
        return [
            'actionUrl', 'apiUrl', 'blueprint', 'dateFormat', 'editUrl', 'fields',
            'handle', 'honeypot', 'path', 'submissions', 'title',
        ];
    }
}
