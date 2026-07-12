<?php

namespace Statamic\GraphQL\Types;

use Statamic\Facades\GraphQL;

class SiteType extends \Rebing\GraphQL\Support\Type
{
    const NAME = 'Site';

    protected $attributes = [
        'name' => self::NAME,
    ];

    public function fields(): array
    {
        return [
            'handle' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
                'resolve' => function ($site) {
                    return $site->handle();
                },
            ],
            'name' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
                'resolve' => function ($site) {
                    return $site->name();
                },
            ],
            'locale' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
                'resolve' => function ($site) {
                    return $site->locale();
                },
            ],
            'short_locale' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
                'resolve' => function ($site) {
                    return $site->shortLocale();
                },
            ],
            'url' => [
                'type' => GraphQL::nonNull(GraphQL::string()),
                'resolve' => function ($site) {
                    return $site->url();
                },
            ],
        ];
    }
}
