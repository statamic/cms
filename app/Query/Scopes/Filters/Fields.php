<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\API\Str;
use Statamic\API\Blueprint;

class Fields extends Filter
{
    public function extra()
    {
        return collect($this->context['blueprints'])
            ->map(function ($blueprint) {
                return Blueprint::find($blueprint);
            })
            ->mapWithKeys(function ($blueprint) {
                return $blueprint->fields()->all()->filter->isFilterable()->map(function ($field) {
                    return [
                        'handle' => $field->handle(),
                        'display' => $field->display(),
                        'type' => $field->type(),
                    ];
                });
            })
            ->values()
            ->all();
    }

    public function apply($query, $values)
    {
        collect($values)
            ->reject(function ($where) {
                return empty($where['value']);
            })
            ->map(function ($where) {
                return $this->normalizeWhere($where);
            })
            ->each(function ($where, $column) use ($query) {
                $query->where($column, $where['operator'], $where['value']);
            });
    }

    protected function normalizeWhere($where)
    {
        if ($where['operator'] === 'like') {
            $where['value'] = Str::ensureLeft($where['value'], '%');
            $where['value'] = Str::ensureRight($where['value'], '%');
        }

        return $where;
    }
}
