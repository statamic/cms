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
        if ($this->hasGroupsRelation()) {
            $method = $boolean == 'or' ? 'orWhereHas' : 'whereHas';
            $this->$method('groups', function ($query) use ($value, $operator) {
                return $query->where('handle', $operator, $value);
            });

            return $this;
        }

        $method = $boolean == 'or' ? 'orWhereExists' : 'whereExists';
        $this->$method(function ($query) use ($operator, $value) {
            $query->select(DB::raw(1))
                ->from($this->groupsTable())
                ->where('groups_id', $operator, $value)
                ->whereColumn($this->groupsTable().'.user_id', 'users.id');
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
        if ($this->hasGroupsRelation()) {
            $method = $boolean == 'or' ? 'orWhereHas' : 'whereHas';
            $this->$method('groups', function ($query) use ($groups) {
                return $query->whereIn('handle', $groups);
            });

            return $this;
        }

        $method = $boolean == 'or' ? 'orWhereExists' : 'whereExists';
        $this->$method(function ($query) use ($groups) {
            $query->select(DB::raw(1))
                ->from($this->groupsTable())
                ->whereIn('groups_id', $groups)
                ->whereColumn($this->groupsTable().'.user_id', 'users.id');
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
        if ($this->hasRolesRelation()) {
            $method = $boolean == 'or' ? 'orWhereHas' : 'whereHas';
            $this->$method('roles', function ($query) use ($value, $operator) {
                return $query->where('handle', $operator, $value);
            });

            return $this;
        }

        $method = $boolean == 'or' ? 'orWhereExists' : 'whereExists';
        $this->$method(function ($query) use ($operator, $value) {
            $query->select(DB::raw(1))
                ->from($this->rolesTable())
                ->where('role_id', $operator, $value)
                ->whereColumn($this->rolesTable().'.user_id', 'users.id');
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
        if ($this->hasRolesRelation()) {
            $method = $boolean == 'or' ? 'orWhereHas' : 'whereHas';
            $this->$method('roles', function ($query) use ($roles) {
                return $query->whereIn('handle', $roles);
            });

            return $this;
        }

        $method = $boolean == 'or' ? 'orWhereExists' : 'whereExists';
        $this->$method(function ($query) use ($roles) {
            $query->select(DB::raw(1))
                ->from($this->rolesTable())
                ->whereIn('role_id', $roles)
                ->whereColumn($this->rolesTable().'.user_id', 'users.id');
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

    private function hasGroupsRelation()
    {
        return method_exists($this->builder->getModel(), 'groups');
    }

    private function hasRolesRelation()
    {
        return method_exists($this->builder->getModel(), 'roles');
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
