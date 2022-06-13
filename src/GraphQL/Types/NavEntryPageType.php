<?php

namespace Statamic\GraphQL\Types;

use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\GraphQL\ResolvesValues;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Blueprint;
use Statamic\Structures\Nav;
use Statamic\Support\Str;

class NavEntryPageType extends \Rebing\GraphQL\Support\Type
{
    protected $nav;
    protected $collection;
    protected $blueprint;

    public function __construct($nav, $collection, $blueprint)
    {
        $this->nav = $nav;
        $this->collection = $collection;
        $this->blueprint = $blueprint;
        $this->attributes['name'] = static::buildName($nav, $collection, $blueprint);
    }

    public static function buildName(Nav $nav, Collection $collection, Blueprint $blueprint): string
    {
        return 'NavEntryPage_'.Str::studly($nav->handle()).'_'.Str::studly($collection->handle()).'_'.Str::studly($blueprint->handle());
    }

    public function interfaces(): array
    {
        return [
            GraphQL::type(PageInterface::NAME),
            GraphQL::type(EntryInterface::NAME),
            GraphQL::type(NavPageInterface::buildName($this->nav)),
        ];
    }

    public function fields(): array
    {
        return collect()
            ->merge($this->blueprint->fields()->toGql())
            ->merge((new NavPageInterface($this->nav))->fields())
            ->merge((new PageInterface)->fields())
            ->merge((new EntryInterface)->fields())
            ->map(function ($arr) {
                if (is_array($arr)) {
                    $arr['resolve'] = $arr['resolve'] ?? $this->resolver();
                }

                return $arr;
            })
            ->all();
    }

    protected function resolver()
    {
        return function (ResolvesValues $page, $args, $context, $info) {
            return $page->resolveGqlValue($info->fieldName);
        };
    }
}
