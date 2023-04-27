<?php

namespace Statamic\Actions;

use Statamic\Contracts\Auth\User as UserContract;

class AssignGroups extends Action
{
    public static function title()
    {
        return __('Assign Groups');
    }

    public function visibleTo($item)
    {
        return $item instanceof UserContract;
    }

    public function authorize($authed, $user)
    {
        return $authed->can('assign user groups');
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Assign groups to this user?|Assign groups to these :count users?';
    }

    public function buttonText()
    {
        /** @translation */
        return 'Assign|Assign to :count users';
    }

    public function run($users, $values)
    {
        $users->each(fn ($user) => $user->groups($values['groups'])->save());
    }

    protected function fieldItems()
    {
        return [
            'groups' => [
                'display' => __('Groups'),
                'type' => 'user_groups',
                'mode' => 'select',
                'validate' => 'required',
            ],
        ];
    }
}
