<?php

namespace Statamic\Actions;

use Statamic\Contracts\Auth\User as UserContract;

class AssignRoles extends Action
{
    public static function title()
    {
        return __('Assign Roles');
    }

    public function visibleTo($item)
    {
        return $item instanceof UserContract;
    }

    public function authorize($authed, $user)
    {
        return $authed->can('assign roles');
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Assign roles to this user?|Assign roles to these :count users?';
    }

    public function buttonText()
    {
        /** @translation */
        return 'Assign|Assign to :count users';
    }

    public function run($users, $values)
    {
        $users->each(fn ($user) => $user->roles($values['roles'])->save());
    }

    protected function fieldItems()
    {
        return [
            'roles' => [
                'display' => __('Roles'),
                'type' => 'user_roles',
                'mode' => 'select',
                'validate' => 'required',
            ],
        ];
    }
}
