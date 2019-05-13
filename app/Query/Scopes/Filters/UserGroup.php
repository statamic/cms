<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\API;

class UserGroup extends Filter
{
    public function fieldItems()
    {
        return [
            'value' => [
                'display' => __('User Group'),
                'type' => 'select',
                'options' => $this->options()
            ]
        ];
    }

    public function apply($query, $values)
    {
        $query->where('group', $values['value']);
    }

    public function visibleTo($key)
    {
        if (empty($this->options())) {
            return false;
        }

        return $key === 'users';
    }

    protected function options()
    {
        return API\UserGroup::all()->mapWithKeys(function ($group) {
            return [$group->handle() => $group->title()];
        })->all();
    }
}
