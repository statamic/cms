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

        if ($groups = $this->params->explode('group', [])) {
            $query->where(function ($query) use ($groups) {
                foreach ($groups as $group) {
                    $query->orWhere('groups/'.$group, true);
                }
            });
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
