<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Support\Facades\DB;
use Statamic\Auth\UserCollection;
use Statamic\Facades\User;
use Statamic\Query\EloquentQueryBuilder;

class UserQueryBuilder extends EloquentQueryBuilder
{
    public function whereGroup($value, $operator = '=', $boolean = 'and')
    {
        $method = $boolean == 'or' ? 'orWhereExists' : 'whereExists';
        $this->$method(function ($query) use ($operator, $value) {
            $query->select(DB::raw(1))
                ->from($this->groupsTable())
                ->where('group_id', $operator, $value)
                ->whereColumn($this->groupsTable().'.user_id', $this->usersTable().'.id');
        });

        return $this;
    }

    public function orWhereGroup($value, $operator = '=')
    {
        $this->whereGroup($value, $operator, 'or');

        return $this;
    }

    public function whereGroupIn($groups, $boolean = 'and')
    {
        $method = $boolean == 'or' ? 'orWhereExists' : 'whereExists';
        $this->$method(function ($query) use ($groups) {
            $query->select(DB::raw(1))
                ->from($this->groupsTable())
                ->whereIn('group_id', $groups)
                ->whereColumn($this->groupsTable().'.user_id', $this->usersTable().'.id');
        });

        return $this;
    }

    public function orWhereGroupIn($groups)
    {
        $this->whereGroupIn($groups, 'or');

        return $this;
    }

    public function whereRole($value, $operator = '=', $boolean = 'and')
    {
        $method = $boolean == 'or' ? 'orWhereExists' : 'whereExists';
        $this->$method(function ($query) use ($operator, $value) {
            $query->select(DB::raw(1))
                ->from($this->rolesTable())
                ->where('role_id', $operator, $value)
                ->whereColumn($this->rolesTable().'.user_id', $this->usersTable().'.id');
        });

        return $this;
    }

    public function orWhereRole($value, $operator = '=')
    {
        $this->whereRole($value, $operator, 'or');

        return $this;
    }

    public function whereRoleIn($roles, $boolean = 'and')
    {
        $method = $boolean == 'or' ? 'orWhereExists' : 'whereExists';
        $this->$method(function ($query) use ($roles) {
            $query->select(DB::raw(1))
                ->from($this->rolesTable())
                ->whereIn('role_id', $roles)
                ->whereColumn($this->rolesTable().'.user_id', $this->usersTable().'.id');
        });

        return $this;
    }

    public function orWhereRoleIn($roles)
    {
        $this->whereRoleIn($roles, 'or');

        return $this;
    }

    protected function transform($items, $columns = ['*'])
    {
        return UserCollection::make($items)->map(function ($model) {
            return User::make()->model($model);
        });
    }

    private function usersTable()
    {
        return config('statamic.users.tables.users', 'users');
    }

    private function groupsTable()
    {
        return config('statamic.users.tables.group_user', 'group_user');
    }

    private function rolesTable()
    {
        return config('statamic.users.tables.role_user', 'role_user');
    }
}
