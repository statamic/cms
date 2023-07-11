<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades\Role;
use Statamic\Query\Scopes\Filter;

class UserRole extends Filter
{
    protected $pinned = true;

    public static function title()
    {
        return __('Role');
    }

    public function fieldItems()
    {
        return [
            'role' => [
                'type' => 'select',
                'placeholder' => __('Select Role'),
                'options' => $this->options()->all(),
            ],
        ];
    }

    public function apply($query, $values)
    {
        if ($values['role'] === 'super') {
            $query->where('super', true);
        } else {
            if(config('statamic.users.repository') == 'eloquent') {
                $tableName = config('statamic.users.tables.role_user', 'role_user');
                $userIds = DB::connection(config('statamic.users.database'))
                    ->table($tableName)
                    ->where('role_id', $values['role'])
                    ->distinct('user_id')
                    ->pluck('user_id')
                    ->toArray();
                    
                $query->whereIn('id', $userIds);
            } else {
                $query->where('roles/'.$values['role'], true);
            }
        }
    }

    public function badge($values)
    {
        return __('Role').': '.strtolower($this->options()->get($values['role']));
    }

    public function visibleTo($key)
    {
        return $key === 'users';
    }

    protected function options()
    {
        return Role::all()->mapWithKeys(function ($role) {
            return [$role->handle() => $role->title()];
        })->prepend('Super', 'super');
    }
}
