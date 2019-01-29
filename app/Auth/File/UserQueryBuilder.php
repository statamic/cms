<?php

namespace Statamic\Auth\File;

use Statamic\API\User;
use Statamic\API\UserGroup;
use Statamic\Data\QueryBuilder;
use Statamic\Auth\UserCollection;

class UserQueryBuilder extends QueryBuilder
{
    protected $group;

    protected function getBaseItems()
    {
        if ($this->group) {
            return UserGroup::find($this->group)->queryUsers()->get();
        }

        return User::all()->values();
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

        return parent::where($column, $operator, $value);
    }
}
