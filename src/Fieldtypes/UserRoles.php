<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Facades\Role;
use Statamic\Facades\Scope;
use Statamic\GraphQL\Types\RoleType;

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

    public function preProcessIndex($data)
    {
        $roles = collect($data)
            ->filter(function ($id) {
                return Role::exists($id);
            })
            ->values()
            ->all();

        return parent::preProcessIndex($roles);
    }

    public function getIndexItems($request)
    {
        return Role::all()->sortBy('title')->map(function ($role) {
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

    public function getSelectionFilters()
    {
        return Scope::filters('user-roles-fieldtype', []);
    }

    public function toGqlType()
    {
        $type = GraphQL::type(RoleType::NAME);

        if ($this->config('max_items') !== 1) {
            $type = GraphQL::listOf($type);
        }

        return $type;
    }
}
