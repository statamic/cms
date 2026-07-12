<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\API\ResourceAuthorizer;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\Form;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\FormType;

class FormsQuery extends Query
{
    protected $attributes = [
        'name' => 'forms',
    ];

    public function type(): Type
    {
        return GraphQL::listOf(GraphQL::type(FormType::NAME));
    }

    public function resolve($root, $args)
    {
        $allowed = ResourceAuthorizer::allowedSubResources('graphql', 'forms');

        return Form::all()->filter(fn ($form) => in_array($form->handle(), $allowed));
    }
}
