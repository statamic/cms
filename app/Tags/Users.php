<?php

namespace Statamic\Tags;

use Statamic\API\User;
use Statamic\API\UserGroup;
use Statamic\Tags\Query;

class Users extends Tags
{
    use Query\HasConditions,
        Query\HasScopes,
        Query\HasOrderBys,
        Query\GetsResults,
        OutputsItems;

    /**
     * {{ get_content from="" }} ... {{ /get_content }}
     */
    public function index()
    {
        $query = $this->query();

        if ($group = $this->get('group')) {
            $query->where('group', $group);
        }

        if ($role = $this->get('role')) {
            $query->where('role', $role);
        }

        return $this->output($this->results($query));
    }

    protected function query()
    {
        $query = User::query();

        $this->queryConditions($query);
        $this->queryScopes($query);
        $this->queryOrderBys($query);

        return $query;
    }
}
