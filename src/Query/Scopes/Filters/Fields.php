<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
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
                $filter = $field->fieldtype()->filter();
                $values = $filter->fields()->addValues($values[$handle])->process()->values();
                $filter->apply($query, $handle, $values);
            });
    }

    public function badge($values)
    {
        return $this->getFields()
            ->filter(function ($field, $handle) use ($values) {
                return isset($values[$handle]);
            })
            ->map(function ($field, $handle) use ($values) {
                $filter = $field->fieldtype()->filter();
                $values = $filter->fields()->addValues($values[$handle])->process()->values();

                return $filter->badge($values);
            })
            ->filter()
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
        if ($collections = Arr::getFirst($this->context, ['collection', 'collections'])) {
            return collect(Arr::wrap($collections))->flatMap(function ($collection) {
                return Collection::findByHandle($collection)->entryBlueprints();
            });
        }

        if ($taxonomies = Arr::getFirst($this->context, ['taxonomy', 'taxonomies'])) {
            return collect(Arr::wrap($taxonomies))->flatMap(function ($taxonomy) {
                return Taxonomy::findByHandle($taxonomy)->termBlueprints();
            });
        }

        return collect();
    }
}
