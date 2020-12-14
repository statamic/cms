<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\GraphQL\Types\GlobalSetInterface;

class GlobalSetQuery extends Query
{
    public function type(): Type
    {
        return GraphQL::type(GlobalSetInterface::NAME);
    }

    public function args(): array
    {
        return [
            'handle' => Type::string(),
        ];
    }

    public function resolve($root, $args)
    {
        $site = Site::default()->handle();

        return GlobalSet::find($args['handle'])->in($site);
    }
}
