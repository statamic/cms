<?php

namespace Statamic\Addons\Suggest\Modes;

use Statamic\API\UserGroup;

class UserGroupsMode extends AbstractMode
{
    public function suggestions()
    {
        $suggestions = [];

        foreach (UserGroup::all() as $group) {
            $suggestions[] = [
                'value' => $group->id(),
                'text'  => $this->label($group, 'title')
            ];
        }

        return $suggestions;
    }
}
