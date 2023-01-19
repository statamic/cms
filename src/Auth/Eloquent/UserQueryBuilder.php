<?php

namespace Statamic\Auth\Eloquent;

use Statamic\Auth\UserCollection;
use Statamic\Facades\User;
use Statamic\Query\EloquentQueryBuilder;

class UserQueryBuilder extends EloquentQueryBuilder
{
    public function whereGroup($value, $operator = '=', $boolean = 'and')
    {
        $method = $boolean == 'or' ? 'orWhereHas' : 'whereHas';
        $this->$method('groups', function ($query) use ($value, $operator) {
            return $query->where('handle', $operator, $value);
        });
    }

    public function orWhereGroup($value, $operator = '=')
    {
        $this->whereGroup($value, $operator, 'or');
    }

    public function whereGroupIn($groups, $boolean = 'and')
    {
        $method = $boolean == 'or' ? 'orWhereHas' : 'whereHas';
        $this->$method('groups', function ($query) use ($groups) {
            return $query->whereIn('handle', $groups);
        });
    }

    public function orWhereGroupIn($groups)
    {
        $this->whereGroupIn($groups, 'or');
    }

    public function whereRole($value, $operator = '=', $boolean = 'and')
    {
        $method = $boolean == 'or' ? 'orWhereHas' : 'whereHas';
        $this->$method('roles', function ($query) use ($value, $operator) {
            return $query->where('handle', $operator, $value);
        });
    }

    public function orWhereRole($value, $operator = '=')
    {
        $this->whereRole($value, $operator, 'or');
    }

    public function whereRoleIn($roles, $boolean = 'and')
    {
        $method = $boolean == 'or' ? 'orWhereHas' : 'whereHas';
        $this->$method('roles', function ($query) use ($roles) {
            return $query->whereIn('handle', $roles);
        });
    }

    public function orWhereRoleIn($roles)
    {
        $this->whereRoleIn($roles, 'or');
    }

    protected function transform($items, $columns = ['*'])
    {
        return UserCollection::make($items)->map(function ($model) {
            return User::make()->model($model);
        });
    }
}
