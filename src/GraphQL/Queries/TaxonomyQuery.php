<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\Taxonomy;
use Statamic\GraphQL\Types\TaxonomyType;

class TaxonomyQuery extends Query
{
    public function type(): Type
    {
        return GraphQL::type(TaxonomyType::NAME);
    }

    public function args(): array
    {
        return [
            'handle' => Type::string(),
        ];
    }

    public function resolve($root, $args)
    {
        return Taxonomy::findByHandle($args['handle']);
    }
}
