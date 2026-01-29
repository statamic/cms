<?php

namespace Statamic\Actions;

use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\UserGroup;

class AssignGroups extends Action
{
    public $icon = 'add-group';

    public static function title()
    {
        return __('Assign Groups');
    }

    public function visibleTo($item)
    {
        return $this->context['view'] === 'list' && $item instanceof UserContract && UserGroup::all()->isNotEmpty();
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
        $users->each(function ($user) use ($values) {
            foreach ($values['groups'] as $group) {
                $user->addToGroup($group);
            }

            $user->save();
        });
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
