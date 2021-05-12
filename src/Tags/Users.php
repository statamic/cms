<?php

namespace Statamic\Tags;

use Statamic\Facades\User;

class Users extends Tags
{
    use Concerns\QueriesConditions,
        Concerns\QueriesScopes,
        Concerns\QueriesOrderBys,
        Concerns\GetsQueryResults,
        Concerns\OutputsItems;

    /**
     * {{ get_content from="" }} ... {{ /get_content }}.
     */
    public function index()
    {
        $query = $this->query();

        if ($group = $this->params->get('group')) {
            $query->where('group', $group);
        }

        if ($role = $this->params->get('role')) {
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
