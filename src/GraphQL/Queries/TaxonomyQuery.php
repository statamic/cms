<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\ResourceAuthorizer;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Taxonomy;
use Statamic\GraphQL\Middleware\AuthorizeSubResources;
use Statamic\GraphQL\Types\TaxonomyType;

class TaxonomyQuery extends Query
{
    protected $attributes = [
        'name' => 'taxonomy',
    ];

    protected $middleware = [
        AuthorizeSubResources::class,
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

    public function subResourceArg()
    {
        return 'handle';
    }

    public function allowedSubResources()
    {
        return ResourceAuthorizer::allowedSubResources('graphql', 'taxonomies');
    }
}
