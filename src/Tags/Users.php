<?php

namespace Statamic\Tags;

use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use Statamic\Tags\Concerns;

class Users extends Tags
{
    use Concerns\QueriesConditions,
        Concerns\QueriesScopes,
        Concerns\QueriesOrderBys,
        Concerns\GetsQueryResults,
        Concerns\OutputsItems;

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
