<?php

namespace Statamic\Forms;

use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Contracts\Forms\Form as FormContract;
use Statamic\CP\Column;
use Statamic\Data\DataCollection;
use Statamic\Facades;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Scope;
use Statamic\Facades\User;
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
            ->withItems(new DataCollection($this->selectableForms()));

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
                'submissions' => $form->querySubmissions()->whereStatus('finalized')->count(),
            ];
        };

        if ($request->boolean('paginate', true)) {
            $forms = $query->paginate();

            $forms->getCollection()->transform($formFields);

            return $forms;
        }

        return $query->get()->map($formFields);
    }

    protected function selectableForms(): Collection
    {
        return Facades\Form::all()->filter(fn ($form) => User::current()->can('view', $form));
    }

    public function preload()
    {
        $data = parent::preload();

        if ($submissions = $this->submissionsPreloadData()) {
            $data['submissions'] = $submissions;
        }

        return $data;
    }

    private function submissionsPreloadData(): ?array
    {
        if ($this->config('max_items') !== 1) {
            return null;
        }

        return $this->submissionsMeta(Arr::first(Arr::wrap($this->field->value())));
    }

    protected function submissionsMeta(?string $handle): ?array
    {
        if (! $form = $this->viewableUniqueInstancesForm($handle)) {
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

    protected function parentEntry(): ?EntryContract
    {
        $parent = $this->field->parent();

        return $parent instanceof EntryContract ? $parent : null;
    }

    protected function uniqueInstancesForm(?string $handle): ?FormContract
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
