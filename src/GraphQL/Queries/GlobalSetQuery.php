<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\ResourceAuthorizer;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Site;
use Statamic\GraphQL\Middleware\AuthorizeSubResources;
use Statamic\GraphQL\Types\GlobalSetInterface;

class GlobalSetQuery extends Query
{
    protected $attributes = [
        'name' => 'globalSet',
    ];

    protected $middleware = [
        AuthorizeSubResources::class,
    ];

    public function type(): Type
    {
        return GraphQL::type(GlobalSetInterface::NAME);
    }

    public function args(): array
    {
        return [
            'handle' => GraphQL::string(),
            'site' => GraphQL::string(),
        ];
    }

    public function resolve($root, $args)
    {
        $site = $args['site'] ?? Site::default()->handle();

        return GlobalSet::find($args['handle'])->in($site);
    }

    public function subResourceArg()
    {
        return 'handle';
    }

    public function allowedSubResources()
    {
        return ResourceAuthorizer::allowedSubResources('graphql', 'globals');
    }
}
