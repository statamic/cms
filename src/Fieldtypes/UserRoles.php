<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\Role;

class UserRoles extends Relationship
{
    protected $canEdit = false;
    protected $canCreate = false;
    protected $statusIcons = false;

    protected function toItemArray($id, $site = null)
    {
        if ($role = Role::find($id)) {
            return [
                'title' => $role->title(),
                'id' => $role->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        return Role::all()->map(function ($role) {
            return [
                'id' => $role->handle(),
                'title' => $role->title(),
            ];
        })->values();
    }

    protected function augmentValue($value)
    {
        return Role::find($value);
    }
}
