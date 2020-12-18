<?php

namespace Statamic\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\InterfaceType;
use Statamic\Structures\Page;

class PageInterface extends InterfaceType
{
    const NAME = 'PageInterface';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'url' => [
                'type' => Type::string(),
            ],
        ];
    }

    public function resolveType(Page $page)
    {
        if (! $page->reference()) {
            return GraphQL::type(PageType::NAME);
        }

        throw new \Exception('todo: resolve entry based page');
    }

    public static function addTypes()
    {
        GraphQL::addType(self::class);
        GraphQL::addType(PageType::class);
    }
}
