<?php

namespace Statamic\GraphQL\Types;

use Statamic\GraphQL\Fields\DateField;

class DateRangeType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'DateRange';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'start' => (new DateField)->setValueResolver(function ($item) {
                return $item['start'];
            }),
            'end' => (new DateField)->setValueResolver(function ($item) {
                return $item['end'];
            }),
        ];
    }
}
