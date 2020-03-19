<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades\Blueprint;
use Statamic\Fields\Field;
use Statamic\Query\Scopes\Filter;
use Statamic\Support\Str;

class Fields extends Filter
{
    public function extra()
    {
        return $this->getFields()
            ->map(function ($field) {
                return [
                    'handle' => $field->handle(),
                    'display' => $field->display(),
                    'config' => $this->filterValueConfig($field),
                    'operators' => $field->fieldtype()->filterOperators(),
                ];
            })
            ->values()
            ->all();
    }

    public function filterValueConfig($field)
    {
        $fields = $field->fieldtype()->filterValueConfig();

        return collect($fields)->map(function ($config, $handle) {
            return isset($config['handle'])
                ? $config
                : (new Field($handle, $config))->toPublishArray();
        })->all();
    }

    public function apply($query, $values)
    {
        collect($values)
            ->reject(function ($where) {
                return empty($where['values']);
            })
            ->each(function ($where, $column) use ($query) {
                $this
                    ->getFields()
                    ->get($column)
                    ->fieldtype()
                    ->filterQuery($query, $column, $where['operator'], $where['values'], $this->context);
            });
    }

    public function badge($values)
    {
        return null;
    }

    public function visibleTo($key)
    {
        return in_array($key, ['entries', 'entries-fieldtype', 'terms']);
    }

    protected function getFields()
    {
        return collect($this->context['blueprints'])->flatMap(function ($blueprint) {
            return Blueprint::find($blueprint)
                ->fields()
                ->all()
                ->filter
                ->isFilterable();
        });
    }
}
