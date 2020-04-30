<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Query\Scopes\Filter;
use Statamic\Support\Arr;

class Fields extends Filter
{
    public static function title()
    {
        return __('Field');
    }

    public function extra()
    {
        return $this->getFields()
            ->map(function ($field) {
                return [
                    'handle' => $field->handle(),
                    'display' => $field->display(),
                    'fields' => $field->fieldtype()->filter()->fields()->toPublishArray(),
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
        return $this->getFields()
            ->filter(function ($field, $handle) use ($values) {
                return isset($values[$handle]);
            })
            ->map(function ($field, $handle) use ($values) {
                return $field->fieldtype()->filter()->badge($values[$handle]);
            })
            ->all();
    }

    public function visibleTo($key)
    {
        return in_array($key, ['entries', 'entries-fieldtype', 'terms']);
    }

    protected function getFields()
    {
        return $this->getBlueprints()->flatMap(function ($blueprint) {
            return $blueprint
                ->fields()
                ->all()
                ->filter
                ->isFilterable();
        });
    }

    protected function getBlueprints()
    {
        if ($collection = Arr::get($this->context, 'collection')) {
            return Collection::findByHandle($collection)->entryBlueprints();
        }

        return collect($this->context['blueprints'])->map(function ($blueprint) {
            return Blueprint::find($blueprint);
        });
    }
}
