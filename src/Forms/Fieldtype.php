<?php

namespace Statamic\Forms;

use Statamic\CP\Column;
use Statamic\Data\DataCollection;
use Statamic\Facades;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Scope;
use Statamic\Fieldtypes\Relationship;
use Statamic\GraphQL\Types\FormType;
use Statamic\Query\ItemQueryBuilder;
use Statamic\Query\Scopes\Filter;

use function Statamic\trans as __;

class Fieldtype extends Relationship
{
    protected static $handle = 'form';
    protected $statusIcons = false;
    protected $canCreate = false;
    protected $canEdit = false;
    protected $canSearch = false;

    protected function configFieldItems(): array
    {
        return [
            'placeholder' => [
                'display' => __('Placeholder'),
                'instructions' => __('statamic::fieldtypes.text.config.placeholder'),
                'type' => 'text',
            ],
            'max_items' => [
                'type' => 'integer',
                'display' => __('Max Items'),
                'default' => 1,
                'instructions' => __('statamic::fieldtypes.form.config.max_items'),
                'force_in_config' => true,
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
        ];
    }

    public function fieldsetContents()
    {
        return [];
    }

    protected function getColumns()
    {
        return [
            Column::make('title'),
            Column::make('submissions'),
        ];
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
            ->withItems(new DataCollection(Facades\Form::all()));

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
