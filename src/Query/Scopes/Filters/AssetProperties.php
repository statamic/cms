<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Fields\Field;
use Statamic\Query\Scopes\Filters\Fields\Filesize;

class AssetProperties extends Fields
{
    public static function title()
    {
        return __('Properties');
    }

    protected function propertyItems(): array
    {
        return [
            'size' => [
                'display' => __('Size'),
                'type' => 'integer',
                'filter' => Filesize::class,
            ],
            'width' => [
                'display' => __('Width'),
                'type' => 'integer',
            ],
        ];
    }

    public function extra()
    {
        return $this->getFilters()
            ->map(function ($filter) {
                return [
                    'handle' => $filter->handle(),
                    'display' => __($filter->display()),
                    'fields' => ($fields = $filter->fields())->toPublishArray(),
                    'meta' => $fields->meta(),
                ];
            })
            ->values()
            ->all();
    }

    protected function getFilters()
    {
        return collect($this->propertyItems())
            ->map(function ($config, $handle) {
                $field = (new Field($handle, $config));

                if ($config['filter'] ?? null) {
                    return app()->make($config['filter'], ['fieldtype' => $field->fieldtype()]);
                } else {
                    return $field->fieldtype()->filter();
                }
            });
    }

    public function apply($query, $values)
    {
        $this->getFilters()
            ->each(function ($filter, $handle) use ($query, $values) {
                if (! isset($values[$handle]) || ! $filter->isComplete($values[$handle])) {
                    return null;
                }
                $values = $filter->fields()->addValues($values[$handle])->process()->values();
                $filter->apply($query, $handle, $values);
            });
    }

    public function badge($values)
    {
        return $this->getFilters()
            ->map(function ($filter, $handle) use ($values) {
                if (! isset($values[$handle]) || ! $filter->isComplete($values[$handle])) {
                    return null;
                }
                $values = $filter->fields()->addValues($values[$handle])->process()->values();

                return $filter->badge($values);
            })
            ->filter()
            ->all();
    }

    public function visibleTo($key)
    {
        return in_array($key, ['assets']);
    }
}
