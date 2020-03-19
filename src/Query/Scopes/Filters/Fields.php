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
                    'config' => $fields->fieldtype()->filter()->fields()->toPublishArray(),
                    'operators' => $field->fieldtype()->filterOperators(),
                ];
            })
            ->values()
            ->all();
    }

    public function apply($query, $values)
    {
        $this->getFields()
            ->filter(function ($field, $handle) use ($values) {
                return isset($values[$handle]);
            })
            ->each(function ($field, $handle) use ($query, $values) {
                $field->fieldtype()->filter()->apply($query, $handle, $values[$handle]);
            });
    }

    public function badge($values)
    {
        return ['test one', 'test two']; // WIP
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
