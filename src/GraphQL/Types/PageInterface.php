<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;
use Statamic\Facades\Nav;

class PageInterface extends EntryInterface
{
    const NAME = 'PageInterface';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function resolveType($page)
    {
        $structure = $page->structure();
        $isNav = $structure instanceof \Statamic\Contracts\Structures\Nav;

        if ($entry = $page->entry()) {
            return GraphQL::type($isNav
                ? NavEntryPageType::buildName($structure, $entry->collection(), $entry->blueprint())
                : EntryPageType::buildName($entry->collection(), $entry->blueprint())
            );
        }

        return GraphQL::type($isNav
            ? NavPageType::buildName($structure)
            : PageType::NAME
        );
    }

    public static function addTypes()
    {
        GraphQL::addType(self::class);
        GraphQL::addType(PageType::class);
        GraphQL::addTypes(array_merge(static::getNavPageTypes(), static::getNavEntryPageTypes()));
    }

    public function fields(): array
    {
        $fields = parent::fields();

        $fields['entry_id'] = [
            'type' => GraphQL::ID(),
        ];

        $fields['title']['type'] = GraphQL::string();

        return $fields;
    }

    protected function extraFields()
    {
        return collect(GraphQL::getExtraTypeFields(static::NAME))
            ->merge(GraphQL::getExtraTypeFields(EntryInterface::NAME));
    }

    private static function getNavPageTypes()
    {
        return Nav::all()->map(function ($nav) {
            return new NavPageType($nav);
        })->all();
    }

    private static function getNavEntryPageTypes()
    {
        return Nav::all()->flatMap(function ($nav) {
            return $nav->collections()->flatMap(function ($collection) use ($nav) {
                return $collection
                    ->entryBlueprints()
                    ->each->addGqlTypes()
                    ->map(function ($blueprint) use ($nav, $collection) {
                        return compact('nav', 'collection', 'blueprint');
                    });
            });
        })->map(function ($item) {
            return new NavEntryPageType($item['nav'], $item['collection'], $item['blueprint']);
        })->all();
    }
}
