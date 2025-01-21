<?php

namespace Statamic\Actions;

use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\Role;

class AssignRoles extends Action
{
    public static function title()
    {
        return __('Assign Roles');
    }

    public function visibleTo($item)
    {
        return $this->context['view'] === 'list' && $item instanceof UserContract && Role::all()->isNotEmpty();
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
        $users->each(function ($user) use ($values) {
            foreach ($values['roles'] as $role) {
                $user->assignRole($role);
            }

            $user->save();
        });
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
