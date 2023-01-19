<?php

namespace Statamic\Stache\Query;

use Statamic\Auth\UserCollection;

class UserQueryBuilder extends Builder
{
    public function whereGroup($value, $operator = '=', $boolean = 'and')
    {
        $this->where('group/'.$value, $operator, true, $boolean);
    }

    public function orWhereGroup($value, $operator = '=')
    {
        $this->whereGroup($value, $operator, 'or');
    }

    public function whereGroupIn($groups, $boolean = 'and')
    {
        $method = $boolean == 'or' ? 'orWhere' : 'where';
        $this->$method(function ($query) use ($groups) {
            foreach ($groups as $group) {
                $query->orWhere('groups/'.$group, true);
            }
        });
    }

    public function orWhereGroupIn($groups)
    {
        $this->whereGroupIn($groups, 'or');
    }

    public function whereRole($value, $operator = '=', $boolean = 'and')
    {
        $this->where('role/'.$value, $operator, true, $boolean);
    }

    public function orWhereRole($value, $operator = '=')
    {
        $this->whereRole($value, $operator, 'or');
    }

    public function whereRoleIn($roles, $boolean = 'and')
    {
        $method = $boolean == 'or' ? 'orWhere' : 'where';
        $this->$method(function ($query) use ($roles) {
            foreach ($roles as $role) {
                $query->orWhere('roles/'.$role, true);
            }
        });
    }

    public function orWhereRoleIn($roles)
    {
        $this->whereRoleIn($roles, 'or');
    }

    protected function getFilteredKeys()
    {
        if (! empty($this->wheres)) {
            return $this->getKeysWithWheres($this->wheres);
        }

        return collect($this->store->paths()->keys());
    }

    protected function getKeysWithWheres($wheres)
    {
        return collect($wheres)->reduce(function ($ids, $where) {
            $keys = $where['type'] == 'Nested'
                ? $this->getKeysWithWheres($where['query']->wheres)
                : $this->getKeysWithWhere($where);

            return $this->intersectKeysFromWhereClause($ids, $keys, $where);
        });
    }

    protected function getKeysWithWhere($where)
    {
        $items = app('stache')
            ->store('users')
            ->index($where['column'])->items();

        $method = 'filterWhere'.$where['type'];

        return $this->{$method}($items, $where)->keys();
    }

    protected function collect($items = [])
    {
        return new UserCollection($items);
    }

    protected function getOrderKeyValuesByIndex()
    {
        return collect($this->orderBys)->mapWithKeys(function ($orderBy) {
            $items = $this->store->index($orderBy->sort)->items()->all();

            return [$orderBy->sort => $items];
        });
    }
}
