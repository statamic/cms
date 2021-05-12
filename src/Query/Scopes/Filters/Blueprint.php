<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Query\Scopes\Filter;
use Statamic\Support\Arr;

class Blueprint extends Filter
{
    public static function title()
    {
        return __('Blueprint');
    }

    public function fieldItems()
    {
        return [
            'blueprint' => [
                'type' => 'select',
                'options' => $this->options()->all(),
                'placeholder' => __('Blueprint'),
            ],
        ];
    }

    public function apply($query, $values)
    {
        $query->where('blueprint', $values['blueprint']);
    }

    public function badge($values)
    {
        return __('Blueprint').': '.$values['blueprint'];
    }

    public function visibleTo($key)
    {
        return in_array($key, ['entries', 'terms'])
            && $this->blueprints()->count() > 1;
    }

    public function blueprints()
    {
        if ($collection = Arr::get($this->context, 'collection')) {
            return Collection::findByHandle($collection)->entryBlueprints();
        }

        if ($taxonomy = Arr::get($this->context, 'taxonomy')) {
            return Taxonomy::findByHandle($taxonomy)->termBlueprints();
        }
    }

    protected function options()
    {
        return $this->blueprints()->mapWithKeys(function ($blueprint) {
            return [$blueprint->handle() => $blueprint->title()];
        });
    }
}
