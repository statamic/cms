<?php

namespace Statamic\Fieldtypes;

use Statamic\CP\Column;
use Statamic\Facades\GraphQL;
use Statamic\Facades\User;
use Statamic\GraphQL\Types\UserType;

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

    protected function configFieldItems(): array
    {
        return [
            'max_items' => [
                'display' => __('Max Items'),
                'instructions' => __('statamic::messages.max_items_instructions'),
                'type' => 'integer',
            ],
            'mode' => [
                'display' => __('Mode'),
                'type' => 'radio',
                'options' => [
                    'default' => __('Stack Selector'),
                    'select' => __('Select Dropdown'),
                    'typeahead' => __('Typeahead Field'),
                ],
                'default' => 'select',
                'width' => 50,
            ],
        ];
    }

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
            if (! $user) {
                return null;
            }

            return [
                'id' => $user->id(),
                'title' => $user->get('name', $user->email()),
                'edit_url' => $user->editUrl(),
                'published' => null,
            ];
        })->filter()->values();
    }

    protected function augmentValue($value)
    {
        return User::find($value);
    }

    protected function shallowAugmentValue($value)
    {
        return $value->toShallowAugmentedCollection();
    }

    protected function getCreateItemUrl()
    {
        return cp_route('users.create');
    }

    public function toGqlType()
    {
        $type = GraphQL::type(UserType::NAME);

        if ($this->config('max_items') !== 1) {
            $type = GraphQL::listOf($type);
        }

        return $type;
    }
}
