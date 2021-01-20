<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class PageInterface extends EntryInterface
{
    const NAME = 'PageInterface';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function resolveType($page)
    {
        if (! $entry = $page->entry()) {
            return GraphQL::type(PageType::NAME);
        }

        return GraphQL::type(
            EntryPageType::buildName($entry->collection(), $entry->blueprint())
        );
    }

    public static function addTypes()
    {
        GraphQL::addType(self::class);
        GraphQL::addType(PageType::class);
    }
}
