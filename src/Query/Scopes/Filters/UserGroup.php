<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades;
use Statamic\Query\Scopes\Filter;

class UserGroup extends Filter
{
    public static function title()
    {
        return __('Group');
    }

    public function fieldItems()
    {
        return [
            'group' => [
                'type' => 'select',
                'placeholder' => __('Select Group'),
                'options' => $this->options()->all(),
            ],
        ];
    }

    public function apply($query, $values)
    {
        $query->where('group', $values['group']);
    }

    public function badge($values)
    {
        return __('Group').': '.strtolower($this->options()->get($values['group']));
    }

    public function visibleTo($key)
    {
        return $key === 'users' && $this->options()->isNotEmpty();
    }

    protected function options()
    {
        return Facades\UserGroup::all()->mapWithKeys(function ($group) {
            return [$group->handle() => $group->title()];
        });
    }
}
