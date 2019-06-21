<?php

namespace Statamic\Fieldtypes;

use Statamic\API\User;
use Statamic\CP\Column;

class Users extends Relationship
{
    protected $statusIcons = false;
    protected $formComponent = 'user-publish-form';

    protected $formComponentProps = [
        'initialTitle' => 'title',
        'initialReference' => 'reference',
        'initialFieldset' => 'blueprint',
        'initialValues' => 'values',
        'initialMeta' => 'meta',
        'actions' => 'actions',
        'canEditPassword' => 'canEditPassword',
    ];

    public function preProcess($data)
    {
        if ($data === 'current') {
            $data = my()->id();
        }

        return parent::preProcess($data);
    }

    protected function toItemArray($id, $site = null)
    {
        if ($user = User::find($id)) {
            return [
                'title' => $user->email(),
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
                'name' => $user->get('name'),
                'email' => $user->email(),
            ];
        })->values();
    }

    protected function getColumns()
    {
        return [
            Column::make('name'),
            Column::make('email'),
        ];
    }

    public function preProcessIndex($data)
    {
        return $this->augment($data)->map(function ($user) use ($data) {
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
