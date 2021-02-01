<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Taxonomy;
use Statamic\GraphQL\Types\TaxonomyType;

class TaxonomiesQuery extends Query
{
    protected $attributes = [
        'name' => 'taxonomies',
    ];

    public function type(): Type
    {
        return GraphQL::listOf(GraphQL::type(TaxonomyType::NAME));
    }

    public function resolve($root, $args)
    {
        return Taxonomy::all();
    }
}
