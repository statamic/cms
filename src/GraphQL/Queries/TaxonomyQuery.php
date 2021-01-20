<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Taxonomy;
use Statamic\GraphQL\Types\TaxonomyType;

class TaxonomyQuery extends Query
{
    protected $attributes = [
        'name' => 'taxonomy',
    ];

    public function type(): Type
    {
        return GraphQL::type(TaxonomyType::NAME);
    }

    public function args(): array
    {
        return [
            'handle' => GraphQL::string(),
        ];
    }

    public function resolve($root, $args)
    {
        return Taxonomy::findByHandle($args['handle']);
    }
}
