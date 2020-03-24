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
        $options = $this->blueprints()->mapWithKeys(function ($blueprint) {
            return [$blueprint->handle() => $blueprint->title()];
        })->all();

        return [
            'blueprint' => [
                'type' => 'select',
                'options' => $options,
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
        return __('blueprint is') . ' ' . $values['blueprint'];
    }

    public function visibleTo($key)
    {
        return $key === 'entries' && $this->blueprints()->count() > 1;
    }

    public function blueprints()
    {
        return Collection::findByHandle($this->context['collection'])->entryBlueprints();
    }
}
