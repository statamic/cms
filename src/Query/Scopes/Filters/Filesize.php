<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Query\Scopes\Filter;

class Filesize extends Filter
{
    public static function title()
    {
        return __('Size');
    }

    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'display' => __('Operator'),
                'placeholder' => __('Select Operator'),
                'hide_display' => true,
                'width' => 50,
                'options' => [
                    '>' => __('Greater than'),
                    '<' => __('Less than'),
                ],
                'default' => '>',
            ],
            'value' => [
                'type' => 'integer',
                'display' => __('Size') . ' (kB)',
                'placeholder' => __('Size') . ' (kB)',
                'hide_display' => true,
                'width' => 50,
                'if' => [
                    'operator' => 'contains_any >, >=, <, <=',
                ],
            ],
        ];
    }

    public function apply($query, $values)
    {
        match ($values['operator']) {
            '>' => $query->where('filesize', '>', $values['value'] * 1024),
            '>=' => $query->where('filesize', '>=', $values['value'] * 1024),
            '<' => $query->where('filesize', '<', $values['value'] * 1024),
            '<=' => $query->where('filesize', '<=', $values['value'] * 1024),
        };
    }

    public function badge($values)
    {
        return sprintf(
            __('Size %s %s kB'),
            $values['operator'],
            $values['value']
        );
    }

    public function visibleTo($key)
    {
        return in_array($key, ['assets']);
    }
}
