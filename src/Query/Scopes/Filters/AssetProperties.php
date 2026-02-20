<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Query\Scopes\Filter;
use Statamic\Support\Str;

class AssetProperties extends Filter
{
    public static function title()
    {
        return __('Properties');
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

    // protected function getFields()
    // {
    //     return [
    //         'size' => [
    //             'handle' => 'size',
    //             'display' => __('Size'),
    //             'type' => new \Statamic\Fieldtypes\Integer,
    //         ],
    //         'size' => [
    //             'handle' => 'size',
    //             'display' => __('Size'),
    //             'type' => ,
    //         ],
    //     ];
    // }

    public function apply($query, $values)
    {
        if (empty($values['value'])) {
            return;
        }

        $query->where('size', $values['operator'], $values['value'] * 1024);
    }

    public function badge($values)
    {
        if (empty($values['value'])) {
            return;
        }

        return sprintf(
            __('Size %s %s'),
            $values['operator'],
            Str::fileSizeForHumans($values['value'] * 1024, 0)
        );
    }

    public function visibleTo($key)
    {
        return in_array($key, ['assets']);
    }
}
