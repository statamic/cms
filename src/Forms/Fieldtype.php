<?php

namespace Statamic\Forms;

use Statamic\CP\Column;
use Statamic\Facades;
use Statamic\Fieldtypes\Relationship;

class Fieldtype extends Relationship
{
    protected static $handle = 'form';
    protected $statusIcons = false;
    protected $canCreate = false;
    protected $canEdit = false;
    protected $canSearch = false;

    protected $configFields = [
        'placeholder' => [
            'type' => 'text',
            'instructions' => 'Set default placeholder text.',
        ],
    ];

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
}
