<?php

namespace Statamic\Forms;

use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\CP\Column;
use Statamic\Data\DataCollection;
use Statamic\Facades;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Fields;
use Statamic\Fieldtypes\Relationship;
use Statamic\GraphQL\Types\FormType;
use Statamic\Query\ItemQueryBuilder;
use Statamic\Query\Scopes\Filter;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Fieldtype extends Relationship
{
    protected static $handle = 'form';
    protected $component = 'form';
    protected $indexComponent = 'form';
    protected $itemComponent = 'form-related-item';
    protected $statusIcons = false;
    protected $canCreate = false;
    protected $canEdit = false;
    protected $canSearch = false;

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance'),
                'fields' => [
                    'placeholder' => [
                        'display' => __('Placeholder'),
                        'instructions' => __('statamic::fieldtypes.text.config.placeholder'),
                        'type' => 'text',
                    ],
                    'mode' => [
                        'display' => __('UI Mode'),
                        'instructions' => __('statamic::fieldtypes.relationship.config.mode'),
                        'type' => 'radio',
                        'default' => 'default',
                        'options' => [
                            'default' => __('Stack Selector'),
                            'select' => __('Select Dropdown'),
                            'typeahead' => __('Typeahead Field'),
                        ],
                    ],
                ],
            ],
            [
                'display' => __('Boundaries & Limits'),
                'fields' => [
                    'max_items' => [
                        'type' => 'integer',
                        'display' => __('Max Items'),
                        'default' => 1,
                        'instructions' => __('statamic::fieldtypes.form.config.max_items'),
                        'force_in_config' => true,
                    ],
                ],
            ],
            [
                'display' => __('Advanced'),
                'fields' => [
                    'query_scopes' => [
                        'display' => __('Query Scopes'),
                        'instructions' => __('statamic::fieldtypes.form.config.query_scopes'),
                        'type' => 'taggable',
                        'options' => Scope::all()
                            ->reject(fn ($scope) => $scope instanceof Filter)
                            ->map->handle()
                            ->values()
                            ->all(),
                    ],
                ],
            ],
        ];
    }

    public function preProcess($data)
    {
        if (! $this->configCanBeOverridden()) {
            return $this->toFormHandles($data);
        }

        return [
            'form' => $this->toFormHandles($data),
            'config' => $this->overrideFields()->addValues(Arr::get($data, 'config', []))->preProcess()->values()->all(),
        ];
    }

    public function preProcessConfig($data)
    {
        $handles = $this->toFormHandles($data);

        return $this->configCanBeOverridden() ? Arr::first($handles) : $handles;
    }

    public function process($data)
    {
        if (! $this->configCanBeOverridden()) {
            return parent::process($this->toFormHandles($data));
        }

        if (! $form = Arr::first($this->toFormHandles($data))) {
            return null;
        }

        $config = $this->overrideFields()
            ->addValues(Arr::get($data, 'config', []))
            ->process()
            ->values()
            ->filter()
            ->all();

        // The limit period always comes back from the publish form since it has a
        // default, but it's only an override when the limit itself is overridden.
        if (empty($config['submission_limit'])) {
            unset($config['submission_limit_period']);
        }

        return $config ? ['form' => $form, 'config' => $config] : $form;
    }

    private function configCanBeOverridden(): bool
    {
        return $this->config('max_items') === 1;
    }

    private function toFormHandles($value): array
    {
        if (is_array($value) && Arr::isAssoc($value)) {
            $value = $value['form'] ?? null;
        }

        return array_values(array_filter(Arr::wrap($value)));
    }

    private function overrideFields(): Fields
    {
        return $this->overrideBlueprint()->fields();
    }

    private function overrideBlueprint(): Blueprint
    {
        $section = ConfigFields::fields()['access'];

        return Facades\Blueprint::make()->setContents([
            'tabs' => ['main' => ['sections' => [[
                'display' => $section['display'],
                'fields' => collect($section['fields'])
                    ->map(fn ($field, $handle) => ['handle' => $handle, 'field' => $field])
                    ->values()
                    ->all(),
            ]]]],
        ]);
    }

    public function rules(): array
    {
        return $this->configCanBeOverridden() ? ['array'] : parent::rules();
    }

    protected function getColumns()
    {
        return [
            Column::make('title'),
            Column::make('submissions'),
        ];
    }

    protected function authorizeItemData($id): bool
    {
        return $this->authorizeViewable(Facades\Form::find($id));
    }

    protected function toItemArray($id, $site = null)
    {
        if ($form = Facades\Form::find($id)) {
            return [
                'title' => __($form->title()),
                'id' => $form->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        $query = (new ItemQueryBuilder())
            ->withItems(new DataCollection(
                Facades\Form::all()->filter(fn ($form) => User::current()->can('view', $form))
            ));

        if ($search = $request->search) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        if ($request->exclusions) {
            $query->whereNotIn('id', $request->exclusions);
        }

        $this->applyIndexQueryScopes($query, $request->all());

        $query->orderBy('title');

        $formFields = function ($form) {
            return [
                'id' => $form->handle(),
                'title' => __($form->title()),
                'submissions' => $form->submissions()->count(),
            ];
        };

        if ($request->boolean('paginate', true)) {
            $forms = $query->paginate();

            $forms->getCollection()->transform($formFields);

            return $forms;
        }

        return $query->get()->map($formFields);
    }

    public function preload()
    {
        $data = parent::preload();

        if ($submissions = $this->submissionsPreloadData()) {
            $data['submissions'] = $submissions;
        }

        if ($configure = $this->configurePreloadData()) {
            $data['configure'] = $configure;
        }

        return $data;
    }

    private function submissionsPreloadData(): ?array
    {
        if (! $form = $this->viewableUniqueInstancesForm(Arr::first($this->toFormHandles($this->field->value())))) {
            return null;
        }

        if (! $entry = $this->parentEntry()) {
            return null;
        }

        return [
            'form' => $form->handle(),
            'filters' => Scope::filters('form-submissions', ['form' => $form->handle(), 'entry' => $entry->id()]),
            'actionUrl' => cp_route('forms.submissions.actions.run', $form->handle()),
        ];
    }

    private function configurePreloadData(): ?array
    {
        if (! $this->configCanBeOverridden()) {
            return null;
        }

        if (! $form = $this->uniqueInstancesForm(Arr::first($this->toFormHandles($this->field->value())))) {
            return null;
        }

        $fields = $this->overrideFields()->addValues(Arr::get($this->field->value(), 'config', []))->preProcess();

        return [
            'form' => $form->handle(),
            'blueprint' => $this->overrideBlueprint()->toPublishArray(),
            'meta' => $fields->meta(),
        ];
    }

    public function preProcessIndex($data)
    {
        return parent::preProcessIndex($data)->map(function ($item) {
            if ($url = $this->submissionsUrl($item['id'])) {
                $item['submissions_url'] = $url;
            }

            return $item;
        });
    }

    private function submissionsUrl(string $handle): ?string
    {
        if (! $form = $this->viewableUniqueInstancesForm($handle)) {
            return null;
        }

        if (! $entry = $this->parentEntry()) {
            return null;
        }

        $filters = base64_encode(json_encode([
            'submission_entry' => ['entry' => $entry->id()],
            'submission_status' => ['status' => 'finalized'],
        ]));

        return $form->submissionsUrl().'?filters='.$filters;
    }

    private function parentEntry(): ?EntryContract
    {
        $parent = $this->field->parent();

        return $parent instanceof EntryContract ? $parent : null;
    }

    private function uniqueInstancesForm(?string $handle): ?FormContract
    {
        if (! $handle || ! $form = Facades\Form::find($handle)) {
            return null;
        }

        return $form->hasUniqueInstances() ? $form : null;
    }

    private function viewableUniqueInstancesForm(?string $handle): ?FormContract
    {
        if (! $form = $this->uniqueInstancesForm($handle)) {
            return null;
        }

        return User::current()->can('viewSubmissions', $form) ? $form : null;
    }

    public function getItemData($values)
    {
        return parent::getItemData($this->toFormHandles($values));
    }

    public function augment($values)
    {
        return parent::augment($this->toFormHandles($values));
    }

    public function shallowAugment($values)
    {
        return parent::shallowAugment($this->toFormHandles($values));
    }

    public function toQueryableValue($value)
    {
        return parent::toQueryableValue($this->toFormHandles($value));
    }

    public function augmentValue($value)
    {
        return Facades\Form::find($value);
    }

    protected function shallowAugmentValue($value)
    {
        return $value->toShallowAugmentedCollection();
    }

    public function toGqlType()
    {
        return GraphQL::type(FormType::NAME);
    }
}
