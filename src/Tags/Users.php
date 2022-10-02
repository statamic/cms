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
            $groups = explode('|', $group);
            $query->where(function ($query) use ($groups) {
                foreach ($groups as $group) {
                    $query->whereJsonContains('groups', $group, 'or');
                }
            });
        }

        if ($role = $this->params->get('role')) {
            $roles = explode('|', $role);
            $query->where(function ($query) use ($roles) {
                foreach ($roles as $role) {
                    $query->whereJsonContains('roles', $role, 'or');
                }
            });
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
