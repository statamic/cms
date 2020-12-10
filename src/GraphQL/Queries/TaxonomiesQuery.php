<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\Taxonomy;
use Statamic\GraphQL\Types\TaxonomyType;

class TaxonomiesQuery extends Query
{
    public function type(): Type
    {
        return Type::listOf(GraphQL::type(TaxonomyType::NAME));
    }

    public function resolve($root, $args)
    {
        return Taxonomy::all();
    }
}
