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
            $query->whereJsonContains('groups', explode('|', $group), 'or');
        }

        if ($role = $this->params->get('role')) {
            $query->whereJsonContains('roles', explode('|', $role), 'or');
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
