<?php

namespace Statamic\Fieldtypes;

use Statamic\CP\Column;
use Statamic\Facades\User;

class Users extends Relationship
{
    protected $statusIcons = false;
    protected $formComponent = 'user-publish-form';
    protected $canEdit = true;

    protected $formComponentProps = [
        'initialTitle' => 'title',
        'initialReference' => 'reference',
        'initialFieldset' => 'blueprint',
        'initialValues' => 'values',
        'initialMeta' => 'meta',
        'actions' => 'actions',
        'canEditPassword' => 'canEditPassword',
    ];

    protected $configFields = [
        'max_items' => [
            'type' => 'integer',
            'default' => 1,
            'instructions' => 'Set a maximum number of selectable users',
        ],
        'mode' => [
            'type' => 'radio',
            'default' => 'select',
            'options' => [
                'default' => 'Stack Selector',
                'select' => 'Select Dropdown',
                'typeahead' => 'Typeahead Field',
            ],
        ],
    ];

    public function preProcess($data)
    {
        if ($data === 'current') {
            $data = User::current()->id();
        }

        return parent::preProcess($data);
    }

    protected function toItemArray($id, $site = null)
    {
        if ($user = User::find($id)) {
            return [
                'title' => $user->name(),
                'id' => $id,
                'edit_url' => $user->editUrl(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        return User::all()->map(function ($user) {
            return [
                'id' => $user->id(),
                'title' => $user->name(),
                'email' => $user->email(),
            ];
        })->values();
    }

    protected function getColumns()
    {
        return [
            Column::make('title')->label('Name'),
            Column::make('email'),
        ];
    }

    public function preProcessIndex($data)
    {
        if (! $data) {
            return collect();
        }

        $users = $this->augment($data);

        if ($this->config('max_items') === 1) {
            $users = collect([$users]);
        }

        return $users->map(function ($user) {
            return [
                'id' => $user->id(),
                'title' => $user->get('name', $user->email()),
                'edit_url' => $user->editUrl(),
                'published' => null,
            ];
        });
    }

    protected function augmentValue($value)
    {
        return User::find($value);
    }

    protected function getCreateItemUrl()
    {
        return cp_route('users.create');
    }
}
