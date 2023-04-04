<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\ResourceAuthorizer;
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
        $allowed = ResourceAuthorizer::allowedSubResources('graphql', 'taxonomies');

        return Taxonomy::all()->filter(fn ($taxonomy) => in_array($taxonomy->handle(), $allowed));
    }
}
