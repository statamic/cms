<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\API\User;
use Statamic\CP\Column;

class Users extends Relationship
{
    protected $statusIcons = false;
    protected $canEdit = false;
    protected $canCreate = false;

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
}
