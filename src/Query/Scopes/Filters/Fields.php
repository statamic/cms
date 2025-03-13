<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Form;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\User;
use Statamic\Query\Scopes\Filter;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Fields extends Filter
{
    protected $pinned = true;

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
                    'display' => __($field->display()),
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
        return in_array($key, ['entries', 'entries-fieldtype', 'form-submissions', 'terms', 'users', 'usergroup-users']);
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

        if ($forms = Arr::getFirst($this->context, ['form', 'forms'])) {
            return collect(Arr::wrap($forms))->map(function ($form) {
                return Form::find($form)
                    ->blueprint()
                    ->ensureField('date', [
                        'type' => 'date',
                        'filterable' => true,
                    ]);
            });
        }

        if (isset($this->context['blueprints'])) {
            return collect($this->context['blueprints'])->map(function ($handle) {
                return $handle === 'user'
                    ? User::blueprint()
                    : Blueprint::find($handle);
            });
        }

        return collect();
    }
}
