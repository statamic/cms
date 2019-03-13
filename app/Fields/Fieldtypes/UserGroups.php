<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\API\UserGroup;

class UserGroups extends Relationship
{
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
        return UserGroup::all()->map(function ($group) {
            return [
                'id' => $group->handle(),
                'title' => $group->title(),
            ];
        })->values();
    }
}
