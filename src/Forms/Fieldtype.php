<?php

namespace Statamic\Forms;

use Statamic\CP\Column;
use Statamic\Facades;
use Statamic\Facades\GraphQL;
use Statamic\Fieldtypes\Relationship;
use Statamic\GraphQL\Types\FormType;

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
                'width' => 50,
            ],
            'max_items' => [
                'type' => 'integer',
                'display' => __('Max Items'),
                'default' => 1,
                'instructions' => __('statamic::fieldtypes.form.config.max_items'),
                'min' => 1,
                'width' => 50,
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
                'title' => $form->title(),
                'id' => $form->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        return Facades\Form::all()->map(function ($form) {
            return [
                'id' => $form->handle(),
                'title' => $form->title(),
                'submissions' => $form->submissions()->count(),
            ];
        })->values();
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
