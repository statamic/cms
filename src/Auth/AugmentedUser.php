<?php

namespace Statamic\Auth;

use Statamic\Data\AbstractAugmented;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use Statamic\Support\Str;

class AugmentedUser extends AbstractAugmented
{
    public function keys()
    {
        return $this->data->data()->keys()
            ->merge(collect($this->data->supplements() ?? [])->keys())
            ->merge($this->commonKeys())
            ->merge($this->roleHandles())
            ->merge($this->groupHandles())
            ->merge($this->blueprintFields()->keys())
            ->unique()->sort()->values()->all();
    }

    private function commonKeys()
    {
        return [
            'id',
            'name',
            'title',
            'email',
            'initials',
            'edit_url',
            'is_user',
            'last_login',
            'avatar',
            'api_url',
        ];
    }

    public function get($handle)
    {
        if ($handle === 'is_user') {
            return true;
        }

        if ($handle === 'is_super') {
            return $this->data->isSuper();
        }

        if (Str::startsWith($handle, 'is_')) {
            return in_array(Str::after($handle, 'is_'), $this->roles());
        }

        if (Str::startsWith($handle, 'in_')) {
            return in_array(Str::after($handle, 'in_'), $this->groups());
        }

        return parent::get($handle);
    }

    protected function roles()
    {
        return $this->data->roles()->map->id()->values()->all();
    }

    protected function groups()
    {
        return $this->data->groups()->map->id()->values()->all();
    }

    protected function roleHandles()
    {
        return Role::all()->map(function ($role) {
            return 'is_'.$role->handle();
        })->values()->all();
    }

    protected function groupHandles()
    {
        return UserGroup::all()->map(function ($group) {
            return 'in_'.$group->handle();
        })->values()->all();
    }

    protected function initials()
    {
        if (! $this->data->hasQueriedColumn('name')) {
            $user = User::query()
                ->where('id', $this->data->id())
                ->get(['name'])
                ->first();

            $this->data->set('name', $user->get('name'));
        }

        return $this->data->initials();
    }

    protected function avatar()
    {
        return $this->data->hasAvatarField() ? $this->data->avatarFieldValue() : $this->data->gravatarUrl();
    }
}
