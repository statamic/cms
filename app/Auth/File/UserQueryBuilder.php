<?php

namespace Statamic\Auth\File;

use Statamic\API\User;
use Statamic\API\UserGroup;
use Statamic\Data\QueryBuilder;
use Statamic\Auth\UserCollection;

class UserQueryBuilder extends QueryBuilder
{
    protected $group;
    protected $role;

    protected function getBaseItems()
    {
        if ($this->group) {
            $users = UserGroup::find($this->group)->queryUsers()->get();
        } else {
            $users = User::all()->values();
        }

        if ($this->role === 'super') {
            $users = $users->filter->isSuper();
        } elseif ($this->role) {
            $users = $users->filter->hasRole($this->role);
        }

        return $users->values();
    }

    protected function collect($items = [])
    {
        return new UserCollection($items);
    }

    public function where($column, $operator = null, $value = null)
    {
        if ($column === 'group') {
            $this->group = $operator;
            return $this;
        }

        if ($column === 'role') {
            $this->role = $operator;
            return $this;
        }

        return parent::where($column, $operator, $value);
    }
}
