<?php

namespace Statamic\Tags;

use Statamic\Facades\User;

class Users extends Tags
{
    use Concerns\GetsQueryResults,
        Concerns\OutputsItems,
        Concerns\QueriesConditions,
        Concerns\QueriesOrderBys,
        Concerns\QueriesScopes;

    /**
     * {{ get_content from="" }} ... {{ /get_content }}.
     */
    public function index()
    {
        $query = $this->query();

        if ($groups = $this->params->explode('group', [])) {
            $query->whereGroupIn($groups);
        }

        if ($roles = $this->params->explode('role', [])) {
            $query->whereRoleIn($roles);
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

    protected function defaultOrderBy()
    {
        return config('statamic.users.sort_field', 'email').':'.config('statamic.users.sort_direction', 'asc');
    }
}
