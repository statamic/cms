<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Facades\Collection;
use Statamic\Query\Scopes\Filter;

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
        return $key === 'entries' && $this->blueprints()->count() > 1;
    }

    public function blueprints()
    {
        return Collection::findByHandle($this->context['collection'])->entryBlueprints();
    }

    protected function options()
    {
        return $this->blueprints()->mapWithKeys(function ($blueprint) {
            return [$blueprint->handle() => $blueprint->title()];
        });
    }
}
