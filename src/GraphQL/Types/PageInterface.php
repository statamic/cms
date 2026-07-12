<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;
use Statamic\Facades\Nav;

class PageInterface extends \Rebing\GraphQL\Support\InterfaceType
{
    const NAME = 'PageInterface';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function resolveType($page)
    {
        $structure = $page->structure();

        $type = ($entry = $page->entry())
            ? NavEntryPageType::buildName($structure, $entry->collection(), $entry->blueprint())
            : NavBasicPageType::buildName($structure);

        return GraphQL::type($type);
    }

    public static function addTypes()
    {
        $types = Nav::all()->flatMap(function ($nav) {
            $types = array_merge([new NavBasicPageType($nav)], static::getNavEntryPageTypes($nav));

            return array_merge([new NavPageInterface($nav)], $types);
        })->all();

        GraphQL::addTypes($types);
    }

    public function fields(): array
    {
        $fields = [
            'id' => [
                'type' => GraphQL::nonNull(GraphQL::id()),
            ],
            'title' => [
                'type' => GraphQL::string(),
            ],
            'url' => [
                'type' => GraphQL::string(),
            ],
            'permalink' => [
                'type' => GraphQL::string(),
            ],
            'entry_id' => [
                'type' => GraphQL::ID(),
            ],
        ];

        foreach (GraphQL::getExtraTypeFields(static::NAME) as $field => $closure) {
            $fields[$field] = $closure();
        }

        return $fields;
    }

    private static function getNavEntryPageTypes($nav)
    {
        return $nav->collections()->flatMap(function ($collection) use ($nav) {
            return $collection
                ->entryBlueprints()
                ->each->addGqlTypes()
                ->map(function ($blueprint) use ($nav, $collection) {
                    return compact('nav', 'collection', 'blueprint');
                });
        })->map(function ($item) {
            return new NavEntryPageType($item['nav'], $item['collection'], $item['blueprint']);
        })->all();
    }
}
