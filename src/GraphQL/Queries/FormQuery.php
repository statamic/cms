<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\ResourceAuthorizer;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\Form;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Middleware\AuthorizeSubResources;
use Statamic\GraphQL\Types\FormType;

class FormQuery extends Query
{
    protected $attributes = [
        'name' => 'form',
    ];

    protected $middleware = [
        AuthorizeSubResources::class,
    ];

    public function type(): Type
    {
        return GraphQL::type(FormType::NAME);
    }

    public function args(): array
    {
        return [
            'handle' => GraphQL::string(),
        ];
    }

    public function resolve($root, $args)
    {
        return Form::find($args['handle']);
    }

    public function subResourceArg()
    {
        return 'handle';
    }

    public function allowedSubResources()
    {
        return ResourceAuthorizer::allowedSubResources('graphql', 'forms');
    }
}
