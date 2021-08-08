<?php

namespace Statamic\GraphQL\Types;

use Statamic\Contracts\GraphQL\ResolvesValues;
use Statamic\Contracts\Structures\Nav;
use Statamic\Facades\GraphQL;
use Statamic\Support\Str;

class NavBasicPageType extends \Rebing\GraphQL\Support\Type
{
    protected $nav;

    public function __construct($nav)
    {
        $this->nav = $nav;
        $this->attributes['name'] = static::buildName($nav);
    }

    public static function buildName(Nav $nav): string
    {
        return 'NavBasicPage_'.Str::studly($nav->handle());
    }

    public function interfaces(): array
    {
        return [
            GraphQL::type(PageInterface::NAME),
            GraphQL::type(NavPageInterface::buildName($this->nav)),
        ];
    }

    public function fields(): array
    {
        return collect()
            ->merge((new NavPageInterface($this->nav))->fields())
            ->merge((new PageInterface)->fields())
            ->map(function ($field) {
                if (is_array($field)) {
                    $field['resolve'] = $this->resolver();
                }

                return $field;
            })
            ->all();
    }

    private function resolver()
    {
        return function (ResolvesValues $page, $args, $context, $info) {
            return $page->resolveGqlValue($info->fieldName);
        };
    }
}
