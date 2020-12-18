<?php

namespace Statamic\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Fields\Value;
use Statamic\Structures\Page;

class PageType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'Page';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function interfaces(): array
    {
        return [
            GraphQL::type(PageInterface::NAME),
        ];
    }

    public function fields(): array
    {
        return collect()
            ->merge((new PageInterface)->fields())
            ->map(function ($field) {
                $field['resolve'] = $this->resolver();

                return $field;
            })
            ->all();
    }

    private function resolver()
    {
        return function (Page $page, $args, $context, $info) {
            $value = $page->augmentedValue($info->fieldName);

            if ($value instanceof Value) {
                $value = $value->value();
            }

            return $value;
        };
    }
}
