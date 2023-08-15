<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Facades\Scope;
use Statamic\Facades\UserGroup;
use Statamic\GraphQL\Types\UserGroupType;

class UserGroups extends Relationship
{
    protected $canEdit = false;
    protected $canCreate = false;
    protected $statusIcons = false;

    protected function toItemArray($id, $site = null)
    {
        if ($group = UserGroup::find($id)) {
            return [
                'title' => $group->title(),
                'id' => $group->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        return UserGroup::all()->sortBy('title')->map(function ($group) {
            return [
                'id' => $group->handle(),
                'title' => $group->title(),
            ];
        })->values();
    }

    protected function augmentValue($value)
    {
        return UserGroup::find($value);
    }

    public function getSelectionFilters()
    {
        return Scope::filters('user-groups-fieldtype', []);
    }

    public function toGqlType()
    {
        $type = GraphQL::type(UserGroupType::NAME);

        if ($this->config('max_items') !== 1) {
            $type = GraphQL::listOf($type);
        }

        return $type;
    }
}
